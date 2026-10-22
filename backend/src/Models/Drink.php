<?php

namespace App\Models;

class Station{

  public function __construct(
    public ?int $id,
    public string $locationName,
    public int $powerKw,
    public bool $isAvailable
  ){}
  
}