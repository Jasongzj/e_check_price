<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductScanRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Map\ErrcodeMap;
use App\Models\Product;
use App\Models\StoreProduct;
use App\Services\ProductService;
use App\Services\QiniuService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    /**
     * 商品列表
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $query = StoreProduct::query()
            ->join('products', function (JoinClause $join) use ($request) {
                $join->on('store_products.product_id', '=', 'products.id');
            })
            ->where('store_id', $user->store_id)
            ->select([
                'store_products.*', 'products.name', 'products.supplier', 'products.price',
                'products.brand', 'products.standard',
            ]);

        if ($name = $request->input('name')) {
            $query->where(function (Builder $query) use ($name) {
                $query->where('products.name', 'like', '%' . $name . '%')
                    ->orWhere('store_products.alias', 'like', '%' . $name . '%');
            });
        }

        $data = $query->paginate();

        return ProductResource::collection($data);
    }

    /**
     * 添加商品
     * @param ProductStoreRequest $request
     * @return mixed
     */
    public function store(ProductStoreRequest $request)
    {
        $product = Product::query()->find($request->input('pid'));
        if (!$product) {
            return $this->notFound('你要添加的商品不存在', 404);
        }
        $store = Auth::guard('api')->user()->store;
        $attribute = $request->only([
            'alias', 'cost_price', 'selling_price',
            'img',
        ]);
        $attribute['barcode'] = $product->barcode;

        $storeProduct = DB::transaction(function () use ($attribute, $product, $store) {
            $storeProduct = new StoreProduct($attribute);
            $storeProduct->product()->associate($product);
            $storeProduct->store()->associate($store);
            $storeProduct->save();
            return $storeProduct;
        });

        $this->formatReturnColumn($storeProduct);

        return $this->success($storeProduct);
    }

    /**
     * 商品详情
     * @param StoreProduct $storeProduct
     * @return mixed
     */
    public function show(StoreProduct $storeProduct)
    {
        $storeProduct->load(['product:id,name,price,brand,supplier,standard']);
        $this->formatReturnColumn($storeProduct);

        return $this->success($storeProduct);
    }

    /**
     * 通过条形码获取商品信息
     * @param ProductScanRequest $request
     * @param ProductService $service
     * @return mixed
     * @throws \App\Exceptions\BarcodeApiException
     */
    public function scan(ProductScanRequest $request, ProductService $service)
    {
        $user = Auth::guard('api')->user();
        $barcode = $request->input('barcode');

        // 查找该店铺是否有该商品
        $storeProduct = StoreProduct::query()
            ->join('products', 'store_products.product_id', '=', 'products.id')
            ->select([
                'store_products.*', 'products.name', 'products.price', 'products.brand',
                'products.supplier', 'products.standard'
            ])
            ->where('store_products.barcode', $barcode)
            ->where('store_id', $user->store_id)
            ->first();

        if (!$storeProduct) {
            // 查找商品表中是否有该商品
            $storeProduct = Product::query()->where('barcode', $barcode)->first();

            if (!$storeProduct) {
                // 请求api，获取商品
                $storeProduct = $service->storeOnlineProduct($barcode);
            }

            $storeProduct->in_store = false;
        } else {
            $storeProduct->in_store = true;
        }

        return $this->success($storeProduct);
    }

    /**
     * 更新商品信息
     * @param ProductUpdateRequest $request
     * @param StoreProduct $storeProduct
     * @return mixed
     */
    public function update(ProductUpdateRequest $request, StoreProduct $storeProduct)
    {
        $attribute = $request->only([
            'alias', 'cost_price', 'selling_price',
            'img',
        ]);

        // 如果更换了图片地址，删除旧图片
        if ($storeProduct->img && $attribute['img'] != $storeProduct->img) {
            $storagePrefix = asset('storage');
            $imgPath = Str::replaceFirst($storagePrefix, storage_path('app/public'), $storeProduct->img);
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }
        $storeProduct->update($attribute);

        $this->formatReturnColumn($storeProduct);

        return $this->success($storeProduct);
    }

    /**
     * 上传商品图片
     * @param Request $request
     * @return mixed
     */
    public function uploadImg(Request $request)
    {
        $qiniuPath = 'store_products/' . Str::random() . '.' . $request->file('img')->getExtension();
        $disk = Storage::disk('qiniu');
        // 上传图片
        $disk->putFile($qiniuPath, $request->file('img'));
        $url = $disk->getUrl($qiniuPath);

        return $this->success($url);
    }

    /**
     * 删除商品
     * @param StoreProduct $storeProduct
     * @return mixed
     * @throws \Exception
     */
    public function destroy(StoreProduct $storeProduct, QiniuService $qiniuService)
    {
        $user = Auth::guard('api')->user();
        if ($storeProduct->store_id != $user->store_id) {
            return $this->forbidden(ErrcodeMap::$errcode[ErrcodeMap::NOT_YOUR_PROD], ErrcodeMap::NOT_YOUR_PROD);
        }

        // 删除商品图
        $qiniuService->deleteFile($storeProduct->getOriginal('img'));
        $storeProduct->delete();

        return $this->success('删除成功');
    }

    /**
     * 统一商品详情返回的字段内容
     * @param StoreProduct $storeProduct
     */
    protected function formatReturnColumn(StoreProduct &$storeProduct)
    {
        $products = $storeProduct->product->toArray();
        // 将通用商品的属性放到同一级
        foreach ($products as $key => $value) {
            if ($key != 'id') {
                $storeProduct->$key = $value;
            }
        }
        unset($storeProduct->product);
    }
}
