<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class EventController extends AbstractController
{
    /*
        Create a new event
    */
    #[Route('/api/events', name: 'create_event', methods: ['POST'])]
    public function createEvent( Request $request, EntityManagerInterface $em, Security $security ): JsonResponse 
    {

        $user = $security->getUser();

        if (!$user || !in_array('ROLE_ORGANIZER', $user->getRoles())) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitle($data['title'] ?? '');
        $event->setDescription($data['description'] ?? '');
        $event->setDate(new \DateTime($data['date'] ?? 'now'));
        $event->setLocation($data['location'] ?? '');
        $event->setUser($user);

        $em->persist($event);
        $em->flush();

        return $this->json(['message' => 'Event created'], 201);
    }


    /*
        list all events
    */
    #[Route('/api/events', name: 'list_events', methods: ['GET'])]
    public function listEvents(EventRepository $eventRepository, Security $security): JsonResponse
    {
        $user = $security->getUser();

        // Si l'utilisateur est connecté et a le rôle ORGANIZER → voir tous les événements
        if ($user && in_array('ROLE_ORGANIZER', $user->getRoles())) {
            $events = $eventRepository->findAll();
        } else {
            // Sinon (utilisateur normal ou anonyme), voir uniquement les événements publiés
            $events = $eventRepository->findBy(['published' => true]);
        }

        $data = array_map(function (Event $event) {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()?->format('Y-m-d H:i:s'),
                'location' => $event->getLocation(),
                'published' => $event->isPublished(),
                'organizer_email' => $event->getUser()?->getEmail(),
            ];
        }, $events);

        return $this->json($data);
    }



    /*
        delete an event by ID
    */
    #[Route('/api/events/{id}', name: 'delete_event', methods: ['DELETE'])]
    public function deleteEvent( Event $event, EntityManagerInterface $em, Security $security ): JsonResponse 
    {

        $user = $security->getUser();

        if ($event->getUser() !== $user || !in_array('ROLE_ORGANIZER', $user->getRoles())) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $em->remove($event);
        $em->flush();

        return $this->json(['message' => 'Event deleted']);
    }



    /*
        Publish an event by ID
    */
    #[Route('/api/events/{id}/publish', name: 'publish_event', methods: ['POST'])]
    public function publish( int $id, EventRepository $eventRepository, EntityManagerInterface $em, Security $security
    ): JsonResponse 
    {

        $event = $eventRepository->find($id);
    
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }
    
        $user = $security->getUser();
    
        // Vérifie que l'utilisateur est l'auteur ET qu'il a le rôle ORGANIZER
        if ($event->getUser() !== $user) {
            return $this->json(['error' => 'You can only publish your own events.'], 403);
        }
    
        if (!in_array('ROLE_ORGANIZER', $user->getRoles())) {
            return $this->json(['error' => 'Only organizers can publish events.'], 403);
        }
    
        if ($event->isPublished()) {
            return $this->json(['message' => 'Event is already published.'], 200);
        }
    
        $event->setPublished(true);
        $em->flush();
    
        return $this->json(['message' => 'Event published successfully.']);
    }


    #[Route('/api/events/{id}', name: 'show_event', methods: ['GET'])]
    public function showEvent(Event $event, Security $security): JsonResponse
    {
        $user = $security->getUser();

        // Si l’événement est publié → tout le monde peut voir
        if ($event->isPublished()) {
            return $this->json($this->formatEvent($event));
        }

        // Si l’événement n’est pas publié → seul son auteur organisateur peut voir
        if ($user && $event->getUser() === $user && in_array('ROLE_ORGANIZER', $user->getRoles())) {
            return $this->json($this->formatEvent($event));
        }

        return $this->json(['error' => 'Event not found or not published'], 404);
    }

    private function formatEvent(Event $event): array
    {
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'date' => $event->getDate()?->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'published' => $event->isPublished(),
            'organizer_email' => $event->getUser()?->getEmail(),
        ];
    }

}
