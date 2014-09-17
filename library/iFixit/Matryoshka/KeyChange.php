<?php

namespace iFixit\Matryoshka;

use iFixit\Matryoshka;

abstract class KeyChange extends Backend {
   private $backend;

   public abstract function changeKey($key);

   /**
    * Convert many keys at once.
    *
    * @param $keys An array of [key => id].
    *
    * @return An array of [changedKey => id].
    */
   public abstract function changeKeys(array $keys);

   public function __construct(Backend $backend) {
      $this->backend = $backend;
   }

   public function set($key, $value, $expiration = 0) {
      return $this->backend->set($this->changeKey($key), $value, $expiration);
   }

   public function setMultiple(array $values, $expiration = 0) {
      return $this->backend->setMultiple($this->changeKeys($values), $expiration);
   }

   public function add($key, $value, $expiration = 0) {
      return $this->backend->add($this->changeKey($key), $value, $expiration);
   }

   public function increment($key, $amount = 1, $expiration = 0) {
      return $this->backend->increment($this->changeKey($key), $amount,
       $expiration);
   }

   public function decrement($key, $amount = 1, $expiration = 0) {
      return $this->backend->decrement($this->changeKey($key), $amount,
       $expiration);
   }

   public function get($key) {
      return $this->backend->get($this->changeKey($key));
   }

   public function getMultiple(array $keys) {
      // Ignore the missed values -- we will recompute them later.
      list($found) = $this->backend->getMultiple($this->changeKeys($keys));

      // Take advantage of the guaranteed ordering of the keys to unchange them.
      $searchKeys = array_keys($keys);
      $foundKeys = array_keys($found);
      $unChangedFound = [];
      $unChangedMissed = [];

      foreach ($searchKeys as $i => $searchKey) {
         $value = $found[$foundKeys[$i]];
         $unChangedFound[$searchKey] = $value;
         if ($value === self::MISS) {
            $unChangedMissed[$searchKey] = $keys[$searchKey];
         }
      }

      return [$unChangedFound, $unChangedMissed];
   }

   public function delete($key) {
      return $this->backend->delete($this->changeKey($key));
   }
}
