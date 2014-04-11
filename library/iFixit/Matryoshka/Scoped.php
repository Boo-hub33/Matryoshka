<?php

namespace iFixit\Matryoshka;

use iFixit\Matryoshka;

class Scoped extends KeyChanger {
   private $backend;
   private $scopeName;
   private $scopePrefix;

   public function __construct(Backend $backend, $scopeName) {
      parent::__construct($backend);

      $this->scopeName = $scopeName;
      $this->backend = $backend;
   }

   public function changeKey($key) {
      $prefix = $this->getScopePrefix();

      return "{$prefix}$key";
   }

   private function getScopePrefix() {
      if ($this->scopePrefix === null) {
         // TODO: This doesn't set an expiration time. Make it user configurable?
         // TODO: This introduces a race condition between the miss and the
         // set.
         $this->scopePrefix = $this->backend->getAndSet($this->getScopeKey(),
          function() {
            return substr(md5(microtime()), 0, 4);
         });
      }

      return $this->scopePrefix;
   }

   public function deleteScope() {
      // TODO: This could probably set a new value in place rather than
      // deleting the current one.
      if ($this->backend->delete($this->getScopeKey())) {
         $this->scopePrefix = null;
         return true;
      } else {
         return false;
      }
   }

   private function getScopeKey() {
      return "scope-{$this->scopeName}";
   }
}
