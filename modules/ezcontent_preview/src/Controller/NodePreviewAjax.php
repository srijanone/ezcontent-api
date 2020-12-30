<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\node\NodeInterface;
use Drupal\ezcontent_preview\Ajax\PreviewContentCommand;

class NodePreviewAjax extends ControllerBase {

  public function showPreviewPanel() {
    $response = new AjaxResponse();
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      $entity = \Drupal\node\Entity\Node::load($node);
      $utils = \Drupal::service('ezcontent_preview.utils');
      $decoupledRoutes = $utils->getNodeDecoupledRoutes($entity);
      if($decoupledRoutes) {
        $response->addCommand(new PreviewContentCommand($decoupledRoutes));
      }
    }
    return $response; 
  }

}