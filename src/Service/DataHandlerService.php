<?php

namespace UserManagement\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DataHandlerService
{
    public function serialize($data)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(1);
        $normalizer->setCircularReferenceHandler(
            function ($object) {
                return $object->getId();
            }
        );
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($data, 'json');
    }

    public function createErrorResponse(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);
        $data = [
            'type' => 'Validation Error',
            'errors' => $errors
        ];

        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
    }

    public function createCustomErrorResponse($errors)
    {
        $data = [
            'type' => 'Validation Error',
            'errors' => $errors
        ];

        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
    }

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

}