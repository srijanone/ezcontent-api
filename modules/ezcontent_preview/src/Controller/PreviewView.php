<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\ezcontent_preview\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preview the content.
 */
class PreviewView extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The utils service.
   *
   * @var \Drupal\ezcontent_preview\Utils
   */
  protected $utils;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, Utils $utils) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
    $this->utils = $utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('ezcontent_preview.utils')
    );
  }

  /**
   * On the basis of config, iframe is shown on Homepage or another tab.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node interface.
   * @param bool $preview_type
   *   The preview type.
   *
   * @return array|\Drupal\Core\Routing\TrustedRedirectResponse
   */
  public function preview(NodeInterface $node, $preview_type) {
    if ($preview_type) {
      $decoupledRoutes = $this->entityTypeManager->getStorage('ezcontent_preview')->load($preview_type);
      if ($decoupledRoutes) {
        $url = $this->utils->buildUrl($node, $decoupledRoutes);
        if ($url) {
          // If new tab open seperate a tab with decoupled URL
          // else just iframe.
          if ($decoupledRoutes->newtab) {
            // Create new Redirect response specifying a cacheable dependency
            // of node.
            $response = new TrustedRedirectResponse($url->toString());
            $response->addCacheableDependency($node);
            return $response;
          }
          $output = '<iframe class="decoupled-content--preview" src="' . $url->toString() . '"></iframe>';
          return [
            '#type' => 'markup',
            '#allowed_tags' => ['iframe'],
            '#markup' => $output,
            '#cache' => [
              'tags' => ['node:' . $node->id()],
            ],
            '#attached' => [
              'library' => [
                'ezcontent_preview/global',
              ],
            ],
          ];
        }
      }
    }

    // If nothing return as URL return empty array.
    return [
      '#markup' => '',
    ];
  }

  /**
   * Custom access validation.
   */
  public function access(AccountInterface $account, $preview_type) {
    $decoupledRoutes = $this->entityTypeManager->getStorage('ezcontent_preview')->load($preview_type);
    $nid = $this->routeMatch->getRawParameter('node');
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    foreach ($decoupledRoutes->content_entity as $entType) {
      if ($node instanceof NodeInterface && $entType === $node->bundle()) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

}
