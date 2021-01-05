<?php

namespace Drupal\ezcontent_preview\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Base Class for PreviewContentCommand.
 */
class PreviewContentCommand implements CommandInterface {

  /**
   * An optional list of available previews.
   *
   * @var array
   */
  protected $previewOptions;

  /**
   * Constructs an InvokeCommand object.
   *
   * @param array $previewOptions
   *   Configs needs to exposed to the frontend.
   */
  public function __construct(array $previewOptions = []) {
    $this->previewOptions = $previewOptions;
  }

  /**
   * Renders Preview Content variables.
   *
   * @return array
   *   Returns Ajax preview content array.
   */
  public function render() {
    return [
      'command' => 'previewContent',
      'preview_options' => $this->previewOptions,
    ];
  }

}
