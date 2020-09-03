<?php

namespace happy;

use Closure;
use think\Request;
use think\Response;
use think\helper\Str;

/**
 * Class ApiConverterMiddleware
 *
 * 1. 将前端请求参数的下划线命名转换为后端的驼峰命名
 * 2. 将后端响应参数的驼峰命名转换为前端的下划线命名
 *
 * @package App\Http\Middleware
 */
class ApiConverterMiddleware
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->convertRequestNameCase($request);
        $response = $next($request);
        $this->convertResponseNameCase($response);
        return $response;
    }

    /**
     * 转换请求参数中的下划线命名转换为驼峰命名
     * @param Request $request
     */
    private function convertRequestNameCase($request)
    {
        $parameters = $request->param();
        $newParameters = [];
        foreach ($parameters as $key => $value) {
            $newParameters[Str::snake($key)] = $value;
        }
        $request->parameters = $newParameters;
    }

    /**
     * 将响应中的参数命名从驼峰命名转换为下划线命名
     * @param Response $response
     */
    private function convertResponseNameCase($response)
    {
        $content = $response->getContent();
        $json = json_decode($content, true);
        if (is_array($json)) {
            $json = $this->recursiveConvertNameCaseToSnake($json);
            $response->content(json_encode($json));
        }
    }

    /**
     * 循环迭代将数组键值转换为下划线格式
     * @param array $arr
     * @return array
     */
    private function recursiveConvertNameCaseToSnake($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $outArr = [];
        foreach ($arr as $key => $value) {
            $outArr[Str::camel($key)] = $this->recursiveConvertNameCaseToSnake($value);
        }
        return $outArr;
    }
}