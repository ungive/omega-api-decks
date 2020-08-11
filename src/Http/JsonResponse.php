<?php

namespace Http;


/**
* A generic class serving as a container for response data and meta information.
*/
class JsonResponse extends \Json\Serializable implements Response
{
    /**
    * @var bool $success Indicates if the request was successful.
    */
    protected bool $success = true;

    /**
    * @var array $meta Associative array with meta data about the request.
    */
    protected array $meta = [];

    /**
    * @var array $data Associative array with response data.
    */
    protected array $data = [];

    protected int $options = 0;

    public function success(bool $success): void { $this->success = $success; }

    public function meta(string $key, $value): void { $this->meta[$key] = $value; }
    public function data(string $key, $value): void { $this->data[$key] = $value; }

    public function get_meta(string $key) { return $this->get('meta', $key); }
    public function get_data(string $key) { return $this->get('data', $key); }

    private function get(string $member, string $key)
    {
        if (!isset($this->$member[$key]))
            throw new \InvalidArgumentException("key '$key' in $member is not set");

        return $this->$member[$key];
    }

    public function options(int $options): void { $this->options = $options; }

    public function to_json(int $options = 0, int $depth = 1 << 9): string
    {
        $options |= $this->options;
        return parent::to_json($options, $depth);
    }

    public function json_serialize()
    {
        $meta = [];
        $data = [];

        $objects = [
            'meta' => &$meta,
            'data' => &$data
        ];

        foreach ($objects as $name => &$object)
            foreach ($this->$name as $key => $value) {
                if ($value instanceof \Json\Serializable)
                    $value = $value->json_serialize();
                $object[$key] = $value;
            }

        return [
            'success' => $this->success,
            'meta' => (object)$meta,
            'data' => (object)$data
        ];
    }

    public static function json_deserialize($value): self
    {
        throw new \Exception("deserializing a response object is not supported");
    }

    public static function mime_type(): string
    {
        return 'application/json';
    }

    public function __toString()
    {
        return $this->to_json();
    }
}
