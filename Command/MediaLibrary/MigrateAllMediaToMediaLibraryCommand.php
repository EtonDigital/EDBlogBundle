<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.6.15.
 * Time: 10.39
 */

namespace ED\BlogBundle\Command\MediaLibrary;


use ED\BlogBundle\Event\MediaArrayEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateAllMediaToMediaLibraryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('edblog:media_migrate_all')
            ->setDescription('Will add all uploaded Media into Media Library Gallery');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        try
        {
            $medias = $this->getContainer()->get('sonata.media.manager.media')->findAll();

            $dispatcher->dispatch(EDBlogEvents::ED_BLOG_MEDIA_LIBRARY_MEDIA_UPLOADED, new MediaArrayEvent($medias));
        }
        catch(\Exception $e)
        {
            $output->writeln($e->getMessage());
        }
    }
}