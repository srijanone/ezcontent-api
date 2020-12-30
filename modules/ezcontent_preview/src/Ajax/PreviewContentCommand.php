<?php

namespace Drupal\ezcontent_preview\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Base Class for PreviewContentCommand.
 */
class PreviewContentCommand implements CommandInterface {

  /**
   * An optional list of available previews
   *
   * @var array
   */
  protected $preview_options;

  /**
   * Constructs an InvokeCommand object.
   *
   * @param string $selector
   *   A jQuery selector.
   * @param string $method
   *   The name of a jQuery method to invoke.
   * @param array $arguments
   *   An optional array of arguments to pass to the method.
   */
  public function __construct(array $preview_options = []) {
    $this->preview_options = $preview_options;
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
      'preview_options' => $this->preview_options,
    ];
  }

}
