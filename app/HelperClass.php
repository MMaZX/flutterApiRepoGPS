
<?php

class ConfigModel
{
    public static function withJsonResponse(int $statusCode, string $message)
    {
        return response()->json([
            "statusCode" => $statusCode,
            "message" => $message,
        ], $statusCode);
    }

    public static function exception(int $statusCode, String  $message)
    {
        throw new Exception($message, $statusCode);
    }
}
