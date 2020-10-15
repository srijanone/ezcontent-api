<?php

namespace Drupal\ezcontent_api\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Implements custom breadcrumb builder.
 */
class EzcontentBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    if ($attributes->getParameter('entity')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $entity = $route_match->getParameter('entity');
    $links = [];
    if ($entity) {
      $links[] = Link::createFromRoute(t('Home'), '<front>');
      $links[] = Link::createFromRoute($entity->type->entity->label(), '<nolink>');
      $links[] = Link::createFromRoute($entity->getTitle(), '<nolink>');
    }
    $breadcrumb = new Breadcrumb();
    // Cache breadcrumb by URL.
    $breadcrumb->addCacheContexts(['url.path']);
    return $breadcrumb->setLinks($links);
  }

}
