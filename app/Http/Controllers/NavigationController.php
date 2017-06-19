<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Object_;

class NavigationController extends Controller
{
    // Controller de navigation

    /**
     * @title Fonction qui renvoit le style d'un élément partie, chapitre ou paragraphe
     * @params objet
     * @return string
     */
    private  function getStyle ($attributs) {
        $style = "";
        if ($attributs['font-weight'] != null) {
            $style .= 'font-weight:' . $attributs['font-weight'] . ";";
        }
        if ($attributs['font-size'] != null) {
            $style .= 'font-size:' . $attributs['font-size'] . ";";
        }

        if ($attributs['font-family'] != null) {
            $style .= 'font-family:' . $attributs['font-family'] . ";";
        }

        if ($attributs['color'] != null) {
            $style .= 'color:' . $attributs['color'] . ";";
        }

        if ($attributs['text-decoration'] != null) {
            $style .= 'text-decoration:' . $attributs['text-decoration'] . ";";
        }
        return $style;
    }

    // Fonction qui renvoit sur la page html représentant le contenu du cours
    /**
     * @return string
     */
    private function getNotions ($notions)
    {
        $nbrNotions = $notions->attributes()->nbrNotions;
        $notionsConvertis = [];
        //array_add($notionsConvertis, 'bon', 'quon');
        //array_push($notionsConvertis, ['key3' => 'value3']);
        //dd($notionsConvertis);
        for ($i = 0; $i < $nbrNotions; $i++) {
            $attributs = $notions->notion[$i]->attributes();
            $style = $this->getStyle($attributs);
            $text = "";
            foreach ($notions->notion[$i]->children() as $child) {
                $text .= $child->asXML();
            }
            $notionHtml = "<div style='";
            $notionHtml .= $style;
            $notionHtml .= "'>";
            $notionHtml .= $text;
            $notionHtml .= "</div>";

            //dd(strval($text));

//            array_push($notionsConvertis, $notionHtml);
//            dd("".$notions->notion[$i]->attributes()->id);
            $id = preg_split("/[_,]+/", "".$notions->notion[$i]->attributes()->id)[3];
            $notionsConvertis[$id] = $notionHtml;
        }
        return $notionsConvertis;
    }


    public function lectureContenu ()
        {
            // on charge le cours

            //$metadata = simplexml_load_file("xmoddledata/metadata.xml");

            // on Vérifie si le cours existe

            /*  @TODO Partie à décommenter lorsque le module de navigation sera intégré au reste de l'application */

            // $nbrCours = $metadata->attributes()->nbrCours;
            // $isFound = false;
            // $i = 0;
            // for ($i = 0; $i < $nbrCours; $i++) {
            //     if ($metadata->cours[$i]->attributes()->title == $title && $metadata->cours[$i]->attributes()->id == $id) {
            //         $isFound = true;
            //         break;
            //     }
            // }

            //  if ($isFound) {
            //$cours = simplexml_load_file("xmoddledata/" . $metadata->cours[$i]->attributes()->id . "_" . $metadata->cours[$i]->attributes()->title . "/description.xml");            

            $description = simplexml_load_file('xmoddledata/2_ModeleDeSupport2/description.xml');
            $notions = simplexml_load_file('xmoddledata/2_ModeleDeSupport2/descriptionNotions.xml');

            // Création d'un tableau associatif de notions avec l'id comme clé

            $notionsArray = $this->getNotions($notions);

            // Construction de la navigation
            $text_parties = "";
            $nav_parties = [];
            for ($i = 0; $i < $description->attributes()->nbrParties; $i++) {
                $text_parties = "<div style='";
                $text_parties .= $this->getStyle($description->partie[$i]->attributes());
                $text_parties .= "'";
                    $text_parties .= " id='".$i."'>";
                    $nav_chapitres = [];
                $text_parties .= $description->partie[$i]->attributes()->title;
                $text_parties .= "</div>";
                $text_parties .= "<br/><br/>";
                $text_chapitres = "";
                for ($j = 0; $j < $description->partie[$i]->attributes()->nbrChapitres; $j++) {
                    $text_chapitres .= "<div style='";
                    $text_chapitres .= $this->getStyle($description->partie[$i]->chapitre[$j]->attributes());
                    $text_chapitres .= "'";
                        $text_chapitres .= " id='".$i."_".$j."'>";
                        $nav_paragraphes = [];
                    $text_chapitres .= $description->partie[$i]->chapitre[$j]->attributes()->title;
                    $text_chapitres .= "</div>";
                    $text_chapitres .= "<br/><br/>";
                    $text_paragraphes = "";
                    for ($k = 0; $k < $description->partie[$i]->chapitre[$j]->attributes()->nbrParagraphes; $k++) {
                        // On renseigne le titre du paragraphe
                        $text_paragraphes .= "<div style='";
                        $text_paragraphes .= $this->getStyle($description->partie[$i]->chapitre[$j]->paragraphe[$k]->attributes());
                        $text_paragraphes .= "'";
                            // Ajout d'un ancre de navigation
                            $text_paragraphes .= " id='".$i."_".$j."_".$k."'>";
                        $text_paragraphes .= $description->partie[$i]->chapitre[$j]->paragraphe[$k]->attributes()->title;
                        $text_paragraphes .= "</div>";
                        $text_paragraphes .= "<br/>";
                            // Navigation avec paragraphes
                            $nav_paragraphes["".$i."_".$j."_".$k] = $description->partie[$i]->chapitre[$j]->paragraphe[$k]->attributes()->title;
                        // On remplit les notions contenus dans le paragraphe
                        for ($l = 0; $l < $description->partie[$i]->chapitre[$j]->paragraphe[$k]->attributes()->nbrNotions; $l++) {
                            $text_paragraphes .= $notionsArray["".$description->partie[$i]->chapitre[$j]->paragraphe[$k]->notion[$l]->attributes()->id];
                        }
                    }
                    $text_chapitres .= $text_paragraphes;
                    $nav_chapitres["".$i."_".$j] = $nav_paragraphes;
                }
                $text_parties .= $text_chapitres;
                $nav_parties["".$i] = $nav_chapitres;
            }
            dd($nav_parties);
//                dd($navigation);
            dd($description);

            // Construction de la page web
            $titre = $description->attributes()->title;
            dd($description);
//                forearch( $navigation->partie[] as $partie) {
//                    dd($pantie);
//                }
            dd($titre);


            // $notions = simplexml_load_file("../../../fichiersdestructuration/descriptionNotions.xml");


            // $nbrNotions = $cours->attributes()->nbrNotions;
            // $notionsConvertis = array();
            // for ($i = 0; $i < $nbrNotions; $i++) {
            //     $attributs = $cours->notion[$i]->attributes();
            //     $style = "";
            //     if ($attributs['font-weight'] != null) {
            //         $style .= 'font-weight:' . $attributs['font-weight'] . ";";
            //     }
            //     if ($attributs['font-size'] != null) {
            //         $style .= 'font-size:' . $attributs['font-size'] . ";";
            //     }

            //     if ($attributs['font-family'] != null) {
            //         $style .= 'font-family:' . $attributs['font-family'] . ";";
            //     }

            //     if ($attributs['color'] != null) {
            //         $style .= 'color:' . $attributs['color'] . ";";
            //     }

            //     if ($attributs['text-decoration'] != null) {
            //         $style .= 'text-decoration:' . $attributs['text-decoration'] . ";";
            //     }

            //     $text = "";
            //     foreach ($cours->notion[$i]->children() as $child) {
            //         $text .= $child->asXML();
            //     }
            //     $notionHtml = "<div style='";
            //     $notionHtml .= $style;
            //     $notionHtml .= "'>";
            //     $notionHtml .= $text;
            //     $notionHtml .= "</div>";


            //     //dd(strval($text));
            //     array_push($notionsConvertis, $notionHtml);

            //     }
            // }else{
            //     return view("/");
            // }
            //$notions = $notionsConvertis;
            // return response()->json($notions,200);

            //  return "lecture cours";
            return 0;
        }


    // Fonction qui renvoie la structure de navigation de la page
    public function lectureNavigation() {
        return "Lecture navigation";
    }

}
