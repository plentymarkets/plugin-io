<?php
namespace IO\Tests\Asserts;

class IsEqualArrayStructure
{
    CONST VALIDATION_MESSAGE_MISSING_KEY = 'Expected key %s is missing in mapped data';


    /**
     * @param array $data
     * @param array $expectedStructure
     * @param string $parentKey
     * @return bool
     * @throws \Exception
     */
    public static function validate($data = [], $expectedStructure = [], $parentKey = '')
    {
        foreach((array)$expectedStructure as $key => $value)
        {
            if ((is_array($data) && !array_key_exists($key, $data)) || (!is_array($data) && !isset($data->{$key})))
            {
                /**
                 * @var \Exception $exception
                 */
                $exception = pluginApp(\Exception::class, [
                    'message' => sprintf(self::VALIDATION_MESSAGE_MISSING_KEY, ltrim($parentKey . '.' . $key, '.') ),
                    'code' => 1,
                    'previous' => null]);

                throw $exception;
            }
            if (is_array($expectedStructure[$key]))
            {
                try
                {

                    $result = self::validate($data[$key] ?? $data->{$key}, $expectedStructure[$key], $parentKey . '.' . $key);
                } catch (\Exception $exception)
                {
                    throw $exception;
                }
            }
        }
        return true;
    }
}
