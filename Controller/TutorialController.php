<?php
namespace Discutea\DTutoBundle\Controller;

use Discutea\DTutoBundle\Controller\Base\BaseTutorialController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Discutea\DTutoBundle\Entity\Tutorial;
use Discutea\DTutoBundle\Entity\Category;
use Discutea\DTutoBundle\Form\Type\TutorialType;

/**
 * TutorialController 
 * 
 * This class contains actions methods for forum.
 * This class extends BaseForumController.
 * 
 * @package  DTutoBundle
 * @author   David Verdier <contact@discutea.com>
 * @access   public
 */
class TutorialController extends BaseTutorialController
{

    /**
     *
     * @Route("/", name="discutea_tuto_homepage")
     * 
     * @return objet Symfony\Component\HttpFoundation\Response
     * 
     */
    public function indexAction()
    {
        $em = $this->getEm();
        $categories = $em->getRepository('DTutoBundle:Category')->findAll();
        
        return $this->render('DTutoBundle:index.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * 
     * @Route("/new/{id}", name="discutea_tuto_create_tutorial")
     * @ParamConverter("category")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     * 
     * @param object $request Symfony\Component\HttpFoundation\Request
     * @param objct $category Discutea\DForumBundle\Entity\Category
     * 
     * @return object Symfony\Component\HttpFoundation\RedirectResponse redirecting moderator's dashboard
     * @return objet Symfony\Component\HttpFoundation\Response
     * 
     */
    public function newTutorialAction(Request $request, Category $category)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $tutorial = new Tutorial();
        $tutorial->setCategory($category);
        $tutorial->setLocale( $request->getLocale() );
        
        $form = $this->createForm(TutorialType::class, $tutorial, array( 'locale' => $request->getLocale() ));

        if ($form->handleRequest($request)->isValid()) {
            // Hydrate contribution
            $contrib = $tutorial->getTmpContrib();
            $contrib->setAuthor($user);
            $contrib->setCurrent(true);

            // Persist Tutorial and Contribution
            $em = $this->getEm();
            $em->persist( $tutorial );
            $em->persist( $contrib );
            $em->flush();
            
            $request->getSession()->getFlashBag()->add('success', $this->getTranslator()->trans('discutea.tuto.tutorial.create'));
            return $this->redirect($this->generateUrl('discutea_tuto_show_tutorial', array('slug' => $tutorial->getSlug())));
        }

        return $this->render('DTutoBundle:Form/tutorial.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * 
     * @Route("/show/{slug}", name="discutea_tuto_show_tutorial")
     * @ParamConverter("tutorial")
     * 
     * @Security("is_granted('CanReadTutorial', tutorial)")
     * 
     */
    public function tutorialAction(Tutorial $tutorial)
    {
        return $this->render('DTutoBundle:tutorial.html.twig', array(
            'tutorial' => $tutorial
        ));     
        
    }
}
