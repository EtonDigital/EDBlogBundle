<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 15.5.15.
 * Time: 08.44
 */

namespace ED\BlogBundle\Command\Article;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveAllArticlesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('edblog:remove_all_articles')
            ->setDescription('It clears Article table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        try
        {
            $em->getRepository("AppBundle:Article")->removeAll();

            $output->writeln("Article table cleared successfully");
        }
        catch(\Exception $e)
        {
            $output->writeln($e->getMessage());
        }
    }

}