<?php

namespace Drupal\ezcontent_api\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\Core\Link;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class EzcontentBreadcrumbNormalizer extends ContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Breadcrumb\Breadcrumb';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $breadcrumbLinks = [];
    foreach ($entity->getLinks() as $link) {
      $breadcrumbLinks[] = [
        'text' => ($link instanceof Link) ? $link->getText() : $link,
        'url' => ($link instanceof Link) ? $link->getUrl()->toString() : '',
      ];
    }
    return $breadcrumbLinks;
  }

}
