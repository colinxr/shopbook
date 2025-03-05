<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Trait HasUuid
 * 
 * This trait provides UUID functionality for models.
 * It automatically generates a UUID for the model when creating a new instance.
 * 
 * Usage:
 * 1. Use this trait in your model
 * 2. Set $incrementing to false
 * 3. Set $keyType to 'string'
 * 4. Set $primaryKey to 'id' or your custom UUID field name
 */
trait HasUuid
{
  /**
   * Boot the trait.
   * 
   * This method is automatically called when the trait is used.
   * It registers the creating event to generate a UUID before saving.
   *
   * @return void
   */
  protected static function bootHasUuid(): void
  {
    static::creating(function (Model $model) {
      $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string) Uuid::uuid4();
    });
  }

  /**
   * Get a new UUID for the model.
   *
   * @return string
   */
  public function newUniqueId(): string
  {
    return (string) Uuid::uuid4();
  }

  /**
   * Get the auto-incrementing key type.
   *
   * @return string
   */
  public function getKeyType(): string
  {
    return 'string';
  }

  /**
   * Get the value indicating whether the IDs are incrementing.
   *
   * @return bool
   */
  public function getIncrementing(): bool
  {
    return false;
  }
}
