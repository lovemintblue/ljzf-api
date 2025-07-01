<?php
/**
 * 用户行为触发异常
 */

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidRequestException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = '', int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            // json() 方法第二个参数就是http 状态码
            return response()->json(['message' => $this->message], $this->code);
        }
    }
}
