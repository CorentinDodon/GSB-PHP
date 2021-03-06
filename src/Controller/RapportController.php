<?php

namespace GSB\Controller;

use DateTime;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use GSB\Domain\Rapport;
use GSB\Form\Type\SaisieType;
use GSB\Form\Type\ListeRapportType;
use GSB\Form\Type\RapportType;
use GSB\Form\Type\EditType;


class RapportController
{
	public function addRapportAction (Request $request, Application $app)
	{
		$praticiens  = $app['dao.praticien']->findAllAsArray();
		$motifs		 = $app['dao.motif']->findAllAsArray();
		$medicaments = $app['dao.medicaments']->findAllAsArray();
		$rapport = new Rapport();
		$rapportForm = $app['form.factory']->create(new SaisieType(), $rapport, [
			'praticiensChoices'  => $praticiens, 
			'motifChoices'       => $motifs,
			'echantillonChoices' => $medicaments,
			]);
		$rapportForm->handleRequest($request);
		if ($rapportForm->isSubmitted() && $rapportForm->isValid()) {
				$app['dao.rapport']->save($rapport,$app);
				$app['session']->getFlashBag()->add('success', 'Le rapport a bien été enregistré.');
		}

		return $app['twig']->render('saisie.html.twig', array(
			'title' 	  => 'Nouveau Rapport',
			'rapportForm' => $rapportForm->createview()));
	}

	public function indexAction (Request $request, Application $app)
	{
		$rapports = $app['dao.rapport']->findMeAsArray($app);
		$rapport = new Rapport();
		$rapportForm = $app['form.factory']->create(new ListeRapportType(), $rapport, [
			'rapportChoices'  => $rapports, 
			]);
		$rapportForm->handleRequest($request);
		return $app['twig']->render('listeRapport.html.twig', array(
			'rapports' 	  => $rapports,
			'title' 	  => 'Detail des rapports',
			'rapportForm' => $rapportForm->createview()));
	}

	public function afficheAction($id, Application $app, Request $request)
	{
		$rapport      = $app['dao.rapport']->find($id);
		$idPraticien  = $rapport->getIdPraticien();
		$idMotif      = $rapport->getIdMotif();
		$praticien    = $app['dao.rapport']->findPraticien($idPraticien);
		$motif        = $app['dao.rapport']->findMotif($idMotif);
		$echantillons = $app['dao.rapport']->findEchantillon($id); 

		$rapport->setIdPraticien($praticien['nom']. ' ' . $praticien['prenom']);
		$rapport->setIdMotif($motif['nom']);
		$rapport->setEchantillon($echantillons);
		

		$rapportForm = $app['form.factory']->create(new RapportType(), $rapport, ['disable' => true]);
		$rapportForm->handleRequest($request);
		return $app['twig']->render('Rapport.html.twig', array(
			'rapport' 	  => $rapport,
			'title' 	  => 'Detail du rapport',
			'rapportForm' => $rapportForm->createview()));

	}

	public function editAction($id, Application $app, Request $request)
	{
		$rapport      = $app['dao.rapport']->find($id);
		$praticiens   = $app['dao.praticien']->findAllAsArray();
		$motifs		  = $app['dao.motif']->findAllAsArray();
		$medicaments  = $app['dao.medicaments']->findAllAsArray();
		$rapport->setDateRap(new DateTime($rapport->getDateRap()));
		$rapport->setDateVisite(new DateTime($rapport->getDateVisite()));
		$rapport->setEchantillon($app['dao.rapport']->findEchantillon($id));
        $strEch = substr(str_replace("  ",";",$rapport->getEchantillon()),1,strlen(str_replace("  ",";",$rapport->getEchantillon())) - 1);
        $strIdEch = "";
        $tmp = explode(";",$strEch);
        foreach($tmp as $rank => $medName){
            $strIdEch = $strIdEch . ";" . $app['dao.medicaments']->findMedicamentByName($medName)['id'];
        }
        $rapport->setStrEchantillon($strIdEch);
        //$strIdEch contient les id des medicaments choisis

		$editForm  = $app['form.factory']->create(new EditType(), $rapport, [
			'praticiensChoices'  => $praticiens, 
			'motifChoices'       => $motifs,
            'echantillonChoices' => $medicaments,
			]);
        //dump($rapport);
		$editForm->handleRequest($request);
		if ($editForm->isSubmitted() && $editForm->isValid()) {
				$app['dao.rapport']->update($rapport,$app);
				$app['session']->getFlashBag()->add('success', 'Le rapport a bien été enregistré.');
                $url = "/".$rapport->getId();
                return $app->redirect($app['url_generator']->generate('listRapport').$url);
		}

		return $app['twig']->render('editRapport.html.twig', array(
			'title' 	  => 'Edition Rapport',
			'editForm'    => $editForm->createview()));
        
	}

}