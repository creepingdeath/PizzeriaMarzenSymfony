<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Database\Database;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AccountController extends Controller {

    private $errorInfo;

    /**
     * @Route("account")
     */
    public function alterUserData(Request $request) {

        // Create a new session and get user id
        $session = new Session();
        $id = $session->get('id_user');

        if (empty($id)) {
            $this->errorInfo = 'Zaloguj się aby uzyskać dostęp do swojego konta.';
            return $this->redirectToRoute('login');
        }

        // Create a new database connection
        try {
            $pdo = new Database();
            $db = $pdo->getDb($pdo->connection());
            $query = $db->prepare("SELECT * FROM user_details WHERE id_user=?");

            $query->bindValue(1, $id);
            $query->execute();

            $userData = $query->fetch();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }

        // Create new form for updating account details
        $user = new User();

        $form = $this->createFormBuilder($user)
                ->add('name', TextType::class, array(
                    'label' => 'Imię',
                    'data' => $userData['name']
                ))
                ->add('lastName', TextType::class, array(
                    'label' => 'Nazwisko',
                    'data' => $userData['lastName']
                ))
                ->add('phoneNumber', NumberType::class, array(
                    'label' => 'Numer telefonu',
                    'data' => $userData['phoneNumber']
                ))
                ->add('city', TextType::class, array(
                    'label' => 'Miasto',
                    'data' => $userData['city']
                ))
                ->add('postalCode', TextType::class, array(
                    'label' => 'Kod pocztowy',
                    'data' => $userData['postalCode']
                ))
                ->add('street', TextType::class, array(
                    'label' => 'Ulica',
                    'data' => $userData['street']
                ))
                ->add('flatNumber', NumberType::class, array(
                    'label' => 'Numer domu',
                    'data' => $userData['houseNumber']
                ))
                ->add('houseNumber', NumberType::class, array(
                    'label' => 'Numer mieszkania',
                    'data' => $userData['flatNumber']
                ))
                ->add('submit', SubmitType::class, ['label' => 'Zmień dane'])
                ->getForm();

        $form->handleRequest($request);

        // Check if form is valid and submitted,
        // then insert/update user data
        if ($form->isValid() && $form->isSubmitted()) {

            $id = $session->get('id_user');
            $user = $form->getData();

            try {
                $query = $db->prepare("INSERT INTO user_details (id_user, name, lastName, phoneNumber, city, postalCode, street, flatNumber, houseNumber) "
                        . "VALUES (:id_user, :name, :lastName, :phoneNumber, :city, :postalCode, :street, :flatNumber, :houseNumber) "
                        . "ON DUPLICATE KEY "
                        . "UPDATE name = :name, lastName = :lastName, phoneNumber = :phoneNumber, city = :city, postalCode = :postalCode, street = :street, flatNumber = :flatNumber, houseNumber = :houseNumber");

                $query->bindValue('id_user', $id);
                $query->bindValue('name', $user->getName());
                $query->bindValue('lastName', $user->getLastName());
                $query->bindValue('phoneNumber', $user->getPhoneNumber());
                $query->bindValue('city', $user->getCity());
                $query->bindValue('postalCode', $user->getPostalCode());
                $query->bindValue('street', $user->getStreet());
                $query->bindValue('flatNumber', $user->getFlatNumber());
                $query->bindValue('houseNumber', $user->getHouseNumber());

                $query->execute();
                $this->errorInfo = 'Poprawnie zmieniono dane.';
            } catch (PDOException $ex) {
                echo $ex->getMessage();
            }
        }
        return $this->render('default/account.html.twig', [
                    'form' => $form->createView(),
                    'errorInfo' => $this->errorInfo
                        ]
        );
    }

}
