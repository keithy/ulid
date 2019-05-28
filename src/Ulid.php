<?php

/*
 * This file is part of the ULID package
 *
 * Copyright (c) 2018 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/uid
 *
 */

namespace Tuupola;

class Ulid
{

    const PAYLOAD_SIZE = 10;
    const PAYLOAD_ENCODED_SIZE = 16;
    const TIMESTAMP_SIZE = 6;
    const TIMESTAMP_ENCODED_SIZE = 10;

    private $payload;
    private $timestamp;

    // Given that the majority of the time an Ulid is used as a string,
    // the current internal representation is arguably the least efficient.
    // This approach to constuction allows future improvements to be made without
    // changing the interface.

    public function __construct($timestamp = null, $payload = null, $alreadyEncoded = false)
    {

        if ($alreadyEncoded) {
            $timestamp = static::crockford()->decode($timestamp, true);
            $payload = static::crockford()->decode($payload, false);
        }

        $this->payload = $payload;
        $this->timestamp = $timestamp;

        if (empty($payload)) {
            $this->payload = random_bytes(self::PAYLOAD_SIZE);
        }
        if (empty($timestamp)) {
            $this->timestamp = time();
        }
    }
    public static function generate()
    {
        return new self;
    }
    public function string()
    {
        return $this->encodeTimeStamp() . $this->encodePayload();
    }
    private function encodePayload()
    {

        $encoded = static::crockford()->encode($this->payload);
        return \str_pad($encoded, self::PAYLOAD_ENCODED_SIZE, "0", STR_PAD_LEFT);
    }
    private function encodeTimeStamp()
    {

        $encoded = static::crockford()->encode($this->timestamp);
        return \str_pad($encoded, self::TIMESTAMP_ENCODED_SIZE, "0", STR_PAD_LEFT);
    }
    public function payload()
    {
        return $this->payload;
    }
    public function timestamp()
    {
        return $this->timestamp;
    }
    public function unixtime()
    {
        return $this->timestamp;
    }
    public function __toString()
    {
        return $this->string();
    }
    //"0123456789ABCDEFGHJKMNPQRSTVWXYZ"
    protected static function crockford()
    {
        static $base32;

        if (!isset($base32)) {
            $base32 = new \Tuupola\Base32([
                "characters" => \Tuupola\Base32::CROCKFORD,
                "padding" => false
            ]);
        }
        return $base32;
    }
    public static function sanitizeString(string $value)
    {
        $value = str_replace(
            ['O', 'L', 'I'],
            ['0', '1', '1'],
            strtoupper($value)
        );

        $value = str_pad($value, 26, "0", STR_PAD_LEFT);

        return static::isStringAnUlid($value) ? $value : false;
    }
    public static function fromString(string $value)
    {

        $timestamp = substr($value, 0, 10);
        $payload = substr($value, 10);

        return new static($timestamp, $payload);
    }
    public static function isStringAnUlid(string $value)
    {
        return (1 === preg_match("/^[0-7][0-9A-HJKMNP-Z]{25}$/", $value));
    }
}
