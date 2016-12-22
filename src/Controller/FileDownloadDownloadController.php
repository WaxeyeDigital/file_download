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
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines a controller to serve file downloads
 */
class FileDownloadDownloadController extends FileDownloadController {

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

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
   * Generates a download
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $scheme
   *   The file scheme, defaults to 'private'.
   * @param \Drupal\file\Entity $fid
   *   The file id
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
    $file_directory = \Drupal::service('file_system')->realpath($scheme . "://");
    $filepath = $file_directory . '/' . $parts[1];
    $filename = $file->getFilename();

    // File doesn't exist
    // This may occur if the download path is used outside of a formatter and the file path is wrong or file is gone
    if (!file_exists($filepath)) {
      throw new NotFoundHttpException();
    }

    $mimetype = Unicode::mimeHeaderEncode($file->getMimeType());
    $headers = array(
      'Content-Type'              => $mimetype,
      'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
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
