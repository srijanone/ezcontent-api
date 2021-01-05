<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\ezcontent_preview\Ajax\PreviewContentCommand;

/**
 * Controller to view preview panel on node view page.
 */
class NodePreviewAjax extends ControllerBase {

  /**
   * Returns the ajax preview panel.
   *
   * @return Response
   *   The ajax configuration array.
   */
  public function showPreviewPanel() {
    $response = new AjaxResponse();
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      $entity = Node::load($node);
      $utils = \Drupal::service('ezcontent_preview.utils');
      $decoupledRoutes = $utils->getNodeDecoupledRoutes($entity);
      if ($decoupledRoutes) {
        $response->addCommand(new PreviewContentCommand($decoupledRoutes));
      }
    }
    return $response;
  }

}
