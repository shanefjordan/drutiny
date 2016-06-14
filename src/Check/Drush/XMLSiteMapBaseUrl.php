<?php

namespace SiteAudit\Check\Drush;

use SiteAudit\Check\Check;
use SiteAudit\Executor\DoesNotApplyException;

class XMLSiteMapBaseUrl extends Check {
  protected function getNamespace()
  {
    return 'variable/xmlsitemap_base_url';
  }
  public function check()
  {
    $context = $check->context;

    // If the module is disabled, then no xmlsitemap.
    if ($check->context->drush->moduleEnabled('xmlsitemap')) {

      // This defaults to $GLOBALS['base_url'] which is bad.
      $base_url = (string) $context->drush->getVariable('xmlsitemap_base_url', '');
      if (empty($base_url)) {
        $check->setToken('base_url', '[empty]');
        return FALSE;
      }

      $check->setToken('base_url', $base_url);
      $pattern = $this->getOption('pattern', '^https?://.+$');
      if (!preg_match("#${pattern}#", $base_url)) {
        return FALSE;
      }
    }
    // If the module is not enabled, then this check does not apply.
    else {
      throw new DoesNotApplyException();
    }

    return TRUE;
  }
}
