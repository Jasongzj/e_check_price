<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StoreProduct;
use App\Services\ProductService;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $query = StoreProduct::query()
            ->join('products', function (JoinClause $join) use ($request) {
                $join->on('store_products.product_id', '=', 'products.id');
                if ($name = $request->input('name')) {
                    $join->where('name', 'like', '%' . $name . '%');
                }
            })
            ->select([
                'store_products.*', 'products.name', 'products.supplier', 'products.price',
                'products.brand', 'products.standard',
            ]);

        if ($name = $request->input('name')) {
            $query->where('alias', 'like', '%' . $name . '%');
        }

        $data = $query->paginate();

        return ProductResource::collection($data);
    }

    /**
     * 添加商品
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $product = Product::query()->find($request->input('pid'));
        if (!$product) {
            return $this->failed('你要添加的商品不存在', 40004);
        }
        $store = Auth::guard('api')->user()->store;
        $attribute = $request->only([
            'alias', 'cost_price', 'selling_price',
            'img',
        ]);
        $attribute['barcode'] = $product->barcode;

        DB::transaction(function () use ($attribute, $product, $store) {
            $storeProduct = new StoreProduct($attribute);
            $storeProduct->product()->associate($product);
            $storeProduct->store()->associate($store);
            $storeProduct->save();
        });

        return $this->message('保存成功');
    }

    /**
     * 商品详情
     * @param StoreProduct $storeProduct
     * @return mixed
     * @throws \App\Exceptions\InvalidHttpException
     */
    public function show(StoreProduct $storeProduct)
    {
        $storeProduct->load(['product:price,brand,supplier,standard']);
        foreach ($storeProduct->product as $key => $value) {
            $storeProduct->$key = $value;
        }
        unset($storeProduct->product);

        return $this->success($storeProduct);
    }

    /**
     * 通过条形码获取商品信息
     * @param Request $request
     * @param ProductService $service
     * @return mixed
     * @throws \App\Exceptions\BarcodeApiException
     */
    public function scan(Request $request, ProductService $service)
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
            $product = Product::query()->where('barcode', $barcode)->first();

            if (!$product) {
                // 请求api，获取商品
                $product = $service->storeOnlineProduct($barcode);
            }
            return $this->notFound('在你的店铺查无该商品', 40003, $product);
        }

        return $this->success($storeProduct);
    }

    /**
     * 更新商品信息
     * @param Request $request
     * @param StoreProduct $storeProduct
     * @return mixed
     */
    public function update(Request $request, StoreProduct $storeProduct)
    {
        $attribute = $request->only([
            'alias', 'cost_price', 'selling_price',
            'img',
        ]);

        // 如果更换了图片地址，删除旧图片
        if ($storeProduct->img && $attribute['img'] != $storeProduct->img) {
            $storagePrefix = asset('storage');
            $imgPath = Str::replaceFirst($storagePrefix, storage_path('app/public'), $storeProduct->img);
            unlink($imgPath);
        }
        $storeProduct->update($attribute);

        return $this->message('更新成功');
    }

    /**
     * 上传商品图片
     * @param Request $request
     * @return mixed
     */
    public function uploadImg(Request $request)
    {
        $path = $request->file('img')->store('products', 'public');
        $url = asset('storage/' . $path);

        return $this->success($url);
    }
}
