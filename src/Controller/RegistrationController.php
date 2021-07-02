<?php


namespace App\Controller; // déclaration de l'espace de nom App/Controller

// utilisation des espaces de noms pour aller y chercher les classes
use App\Entity\User; 
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController 
// Déclaration de la classe RegistrationController
//RegistrationController est une extension de la classe de base AbstractController
{
    private $emailVerifier; // propriété ou attribut privé(e) à l'intérieur de la classe

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier; 
        // on utilise $this pour pouvoir inclure la propriété privée $emailVerifier dans la méthode __construct
        // interne à la classe
        // la méthode affecte la valeur de la propriété $emailVerifier au champ emailVerifier de l'objet qui 
        // instancie la classe
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator): Response
    {
        $user = new User(); // déclaration de l'instance $user de la classe User
        $form = $this->createForm(RegistrationFormType::class, $user); // Déclaration du formulaire
        $form->handleRequest($request); // Vérifie ce qui est entré dans le formulaire et appelle la fonction submit
                                        // si c'est ok

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            ); // Si le formulaire est ok est que le submit est confirmé, alors l'objet $user est hydraté 
               // et le mot de passe est encrypté 

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@company.com', 'company.com'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    // crée une URL et l'envoit à l'email de l'utilsateur
            );
            // do anything else you need here, like send an email

           
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user, // Authentifie l'utilisateur et envoie une réponse positive si utilisateur authentifié
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        } 
        if ($this->getuser()) {
            return $this->render('home/home.html.twig'); // rendu du template home
        } else {
            return $this->render('registration/register.html.twig', [ // rendu du template register
            'registrationForm' => $form->createView(), // création de la vue
        ]);
        }
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
