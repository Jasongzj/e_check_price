<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-07-23
 * Time: 19:49
 */

namespace App\Services;

use App\Exceptions\BarcodeApiException;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductService extends AbstractService
{
    /**
     * 保存在线api返回的商品信息
     * @param $barcode
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     * @throws BarcodeApiException
     */
    public function storeOnlineProduct($barcode)
    {
        $url = 'https://www.mxnzp.com/api/barcode/goods/details';

        $result = $this->getHttpClient()->get($url, ['query' => ['barcode' => $barcode]]);
        $response = json_decode($result->getBody()->getContents(), true);

        if (!$response['code']) {
            Log::error('请求条形码api失败，错误原因：' . $response['msg']);
            throw new BarcodeApiException('条形码识别失败');
        }

        // 保存商品至数据库
        $data = $response['data'];
        $data['name'] = $data['goodsName'];
        // 过滤空值
        $data = array_filter($data);
        $product = Product::query()->create($data);

        return $product;
    }
}
