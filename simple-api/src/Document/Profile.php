<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Profile
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="integer")
     */
    protected $userId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $address;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $phone;

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }
}
