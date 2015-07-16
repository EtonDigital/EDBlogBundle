<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 26.6.15.
 * Time: 10.05
 */

namespace ED\BlogBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BlogUsersPromoteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('edblog:user:synchronize_blog_users')
            ->setDescription('Will add ROLE_BLOG_USER to existing ROLE_BLOG_ADMIN users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        try
        {
            $buffer = 0;

            foreach( $this->getContainer()->get('app_repository_user')->findAll() as $user)
            {
                if($user->hasRole('ROLE_BLOG_ADMIN') && !$user->hasRole('ROLE_BLOG_USER'))
                {
                    $user->addRole('ROLE_BLOG_USER');
                    $em->persist($user);

                    if(++$buffer == 50)
                    {
                        $buffer = 0;
                        $em->flush();
                    }
                }
            }

            $em->flush();
        }
        catch(\Exception $e)
        {
            $output->writeln($e->getMessage());
        }
    }
}