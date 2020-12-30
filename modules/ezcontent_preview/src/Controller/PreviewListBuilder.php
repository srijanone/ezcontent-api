<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing.
 */
class PreviewListBuilder extends DraggableListBuilder {

  public function getFormID() {
    return 'decoupled_preview_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    $row['label'] = $entity->label;
    return $row + parent::buildRow($entity);
  }

}
