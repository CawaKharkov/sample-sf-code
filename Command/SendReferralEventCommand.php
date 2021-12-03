<?php

namespace App\Command;

use App\Repository\ReferralEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendReferralEventCommand extends Command
{
    protected static $defaultName = 'app:send-referral-event';
    private $client;
    private $repository;
    private $em;

    public function __construct(Client $client, ReferralEventRepository $repo, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->repository = $repo;
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send referral events')
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*$io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');*/

        $events = $this->repository->findBy([
            'accepted' => false
        ]);

        $headers = [
            'Content-Type' => 'application/json;charset=UTF-8',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $2y$12$12LeBuUkpARRKx0ZV3gL/OXZ.B5qk17haWfE61vKuDfRSNxnBIUSG'
        ];
        //  {“even””:”event_code”,”email”:”user_email”,”event_date”:”timestamp”,data:{some_json_data}}

        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            // application/json
            echo $request->getBody();
            // {"foo":"bar"}
        });

        foreach ($events as $event) {
            $json = [
                'event' => $event->getEvent(),
                'event_date' => $event->getEventDate(),
                'email' => $event->getEmail(),
                'data' => json_decode( $event->getEventData())
            ];

            //dump($json); die();
            $res = $this->client->post('http://168.119.238.150/api/ref-events', [
                'headers' => $headers,
                'json' => $json
                //'handler' => $tapMiddleware($clientHandler)
            ]);

            if ($res->getStatusCode() == 200) {
                $event->setAccepted(true);
                $this->em->persist($event);
                $this->em->flush();
            }
        }

    }
}
