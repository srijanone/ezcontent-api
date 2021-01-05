<?php

namespace Drupal\ezcontent_preview\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ezcontent_preview\PreviewInterface;

/**
 * Defines the Preview entity.
 *
 * @ConfigEntityType(
 *   id = "ezcontent_preview",
 *   label = @Translation("Ezcontent Preview"),
 *   handlers = {
 *     "list_builder" = "Drupal\ezcontent_preview\Controller\PreviewListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ezcontent_preview\Form\PreviewForm",
 *       "edit" = "Drupal\ezcontent_preview\Form\PreviewForm",
 *       "delete" = "Drupal\ezcontent_preview\Form\PreviewDeleteForm",
 *     }
 *   },
 *   config_prefix = "ezcontent_preview",
 *   admin_permission = "EZContent preview config",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "url" = "url",
 *     "token_time" = "token_time",
 *     "content_entity" = "content_entity",
 *     "weight" = "weight"
 *   },
 *  config_export = {
 *     "id",
 *     "url",
 *     "label",
 *     "token_time",
 *     "content_entity",
 *     "weight"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/ezcontent_preview/{preview}",
 *     "delete-form" = "/admin/config/system/ezcontent_preview/{preview}/delete",
 *   }
 * )
 */
class Preview extends ConfigEntityBase implements PreviewInterface {

  /**
   * Defining id.
   *
   * @var string
   */
  public $id;

  /**
   * Defining url.
   *
   * @var string
   */
  public $url;

  /**
   * Defining label.
   *
   * @var string
   */
  public $label;

  /**
   * Defining tokenTime.
   *
   * @var string
   */
  public $token_time;

  /**
   * Defining contentEntity.
   *
   * @var string
   */
  public $content_entity;

  /**
   * Defining contentEntity.
   *
   * @var int
   */
  public $weight;

}
