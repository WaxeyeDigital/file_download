<?php

namespace Drupal\file_download\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\system\FileDownloadController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Defines a controller to serve image styles.
 */
class FileDownloadDownloadController extends FileDownloadController {

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a ImageStyleDownloadController object.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   */
//  public function __construct(LockBackendInterface $lock, ImageFactory $image_factory) {
//    $this->lock = $lock;
//    $this->imageFactory = $image_factory;
//    $this->logger = $this->getLogger('image');
//  }

  /**
   * {@inheritdoc}
   */
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('lock'),
//      $container->get('image.factory')
//    );
//  }

  /**
   * Generates a derivative, given a style and image path.
   *
   * After generating an image, transfer it to the requesting agent.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $scheme
   *   The file scheme, defaults to 'private'.
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style to deliver.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
   *   The transferred file as response or some error response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user does not have access to the file.
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   *   Thrown when the file is still being generated.
   */
  public function deliver(Request $request, $scheme, $fid) {
    $file = File::load($fid);
    $uri = $file->getFileUri();
    $parts = explode('://', $uri);
    $filepath = \Drupal::service('file_system')->realpath($scheme . "://");
    $uri = $filepath . '/' . $parts[1];

    $headers = array(
      'Content-Type'              => 'force-download',
      'Content-Disposition'       => 'attachment; filename="' . $file->getFilename() . '"',
      'Content-Length'            => $file->getSize(),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma'                    => 'no-cache',
      'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
      'Expires'                   => '0',
      'Accept-Ranges'             => 'bytes'
    );

    // Update file counter
    if (\Drupal::moduleHandler()->moduleExists('file_download_counter')) {
      $count_downloads = \Drupal::config('file_download_counter.settings')->get('count_downloads');
      if ($count_downloads) {
        file_download_counter_increment_file($fid);
      }
    }

    // \Drupal\Core\EventSubscriber\FinishResponseSubscriber::onRespond()
    // sets response as not cacheable if the Cache-Control header is not
    // already modified. We pass in FALSE for non-private schemes for the
    // $public parameter to make sure we don't change the headers.
    return new BinaryFileResponse($uri, 200, $headers, $scheme !== 'private');
  }

}
