<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\TODO;
use Doctrine\ORM\EntityManagerInterface;

class addTODOCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-todo';

    private $em;
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;

        // In a Command, you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setDescription('Crear un nuevo TODO.')
        ->setHelp('Este comando permite crear un TODO')
        ->addArgument('name', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        try {
            $todo = new TODO($name);
            $this->em->persist($todo);
            $this->em->flush();

            $output->writeln("Nuevo TODO creado con id ".$todo->getId());
        } catch (\Throwable $th) {
            //throw $th;
            $output->writeln("ERROR: NO SE HA PODIDO CREAR UN NUEVO TODO");
        }
        
    }
}