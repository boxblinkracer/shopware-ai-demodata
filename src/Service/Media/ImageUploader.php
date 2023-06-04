<?php

namespace AIDemoData\Service\Media;


use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\File\FileFetcher;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;


class ImageUploader
{

    /**
     * @var EntityRepository
     */
    private $mediaRepository;

    /**
     * @var EntityRepository
     */
    private $mediaFolderRepository;

    /**
     * @var FileSaver
     */
    private $fileSaver;

    /**
     * @var FileFetcher
     */
    private $fileFetcher;


    /**
     * @param EntityRepository $mediaRepository
     * @param EntityRepository $mediaFolderRepository
     * @param FileSaver $fileSaver
     * @param FileFetcher $fileFetcher
     */
    public function __construct(EntityRepository $mediaRepository, EntityRepository $mediaFolderRepository, FileSaver $fileSaver, FileFetcher $fileFetcher)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->fileSaver = $fileSaver;
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * Copied from "vendor/shopware/core/Content/Media/MediaService.php".
     */
    public function getDefaultFolder(string $folderName): ?MediaFolderEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('media_folder.defaultFolder.entity', $folderName))
            ->addAssociation('defaultFolder')
            ->setLimit(1);

        return $this->mediaFolderRepository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }

    public function upload(string $mediaId, string $folderId, string $filename, string $extension, string $contentType): void
    {
        $ctx = Context::createDefaultContext();

        $this->mediaRepository->upsert([
            [
                'id' => $mediaId,
                'mediaFolderId' => $folderId,
            ],
        ],
            $ctx
        );

        $uploadedFile = $this->fileFetcher->fetchBlob(
            (string)file_get_contents($filename),
            $extension,
            $contentType
        );

        $this->fileSaver->persistFileToMedia(
            $uploadedFile,
            basename($filename, '.' . $extension),
            $mediaId,
            $ctx
        );
    }

}