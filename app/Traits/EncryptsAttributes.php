<?php

namespace App\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

trait EncryptsAttributes
{
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptedAttributes ?? [])) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptedAttributes ?? [])) {
            try {
                $value = Crypt::decryptString($value);
            } catch (DecryptException $e) {
                $value = null;
            }
        }

        return $value;
    }

    public function getPlainAttribute($key)
    {
        if (!in_array($key, $this->encryptedAttributes ?? [])) {
            return $this->getAttribute($key);
        }

        $value = $this->getRawOriginal($key);
        
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return null;
        }
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->encryptedAttributes ?? [] as $key) {
            if (isset($attributes[$key])) {
                try {
                    $attributes[$key] = Crypt::decryptString($attributes[$key]);
                } catch (DecryptException $e) {
                    $attributes[$key] = null;
                }
            }
        }

        return $attributes;
    }
}