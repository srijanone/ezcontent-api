<?php

namespace Drupal\ezcontent_jsonapi_extension\Plugin\jsonapi\resource;

use Drupal\jsonapi\ResourceResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\media\Entity\Media;

/**
 * Provides a custom JSON:API resource.
 *
 * @Resource(
 *   id = "ez_resource_media_image",
 *   label = @Translation("EZ Content Resource - Media Image Api to return image url from id"),
 *   uri_paths = {
 *     "canonical" = "/jsonapi/media/image/{machine_name}/{id}"
 *   }
 * )
 */
class EzResourceMediaImage extends ResourceResponse {

  /**
   * Returns JsonResponse url from media id.
   *
   * @param string $machine_name
   *   The machine name of media.
   * @param string $id
   *   The id of media file.
   */
  public function get($machine_name, $id) {
    $media = Media::load($id);
    if ($media && $media->bundle() && $media->bundle() == 'image' && $media->get($machine_name)) {
      $file_entity = $media->get($machine_name)->entity;
      if ($file_entity) {
        $url = $file_entity->createFileUrl();
        return new JsonResponse(['url' => $url]);
      }
    }
    else {
      return new JsonResponse(['error' => 'File not found or is not an image.'], 404);
    }

  }

}
