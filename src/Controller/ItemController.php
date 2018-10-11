<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11/10/17
 * Time: 16:07
 * PHP version 7
 */

namespace Controller;

use Model\Item;
use Model\ItemManager;

/**
 * Class ItemController
 *
 */
class ItemController extends AbstractController
{


    /**
     * Display item listing
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $itemManager = new ItemManager($this->getPdo());
        $items = $itemManager->selectAll();

        return $this->twig->render('Item/index.html.twig', ['items' => $items]);
    }


    /**
     * Display item informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function show(int $id)
    {
        $itemManager = new ItemManager($this->getPdo());
        $item = $itemManager->selectOneById($id);

        return $this->twig->render('Item/show.html.twig', ['item' => $item]);
    }


    /**
     * Display item edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function edit(int $id): string
    {
        $itemManager = new ItemManager($this->getPdo());
        $item = $itemManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item->setTitle($_POST['title']);
            $itemManager->update($item);
        }

        return $this->twig->render('Item/edit.html.twig', ['item' => $item]);
    }


    /**
     * Display item creation page
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function add()
    {
        $errors=array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if($_POST){

                if (empty($_POST['highlight'])) {
                    $_POST['highlight']="off";
                }

                if(empty($_POST['name'])) {
                    $errors['name'] = 'Veuillez remplir le champ "Nom';
                }
                if(empty($_POST['category'])) {
                    $errors['category'] = 'Veuillez remplir le champ "Catégorie"';
                }
                if(empty($_POST['price'])) {
                    $errors['price'] = 'Veuillez remplir le champ "Prix"';
                }
                if(empty($_POST['description'])) {
                    $errors['description'] = 'Veuillez remplir le champ "Description"';
                }

                if (!preg_match("/^[a-zA-Z0-9 ]+$/", $_POST['name'])){
                    $errors['name'] = 'Veuillez remplir le champ "Nom" uniquement avec des caractères alphanumériques';
                }
                if (!preg_match("/^[a-zA-Z0-9 \.\(\)]+$/", $_POST['description'])){
                    $errors['description'] = 'Veuillez remplir le champ "Description" uniquement avec des caractères alphanumériques et les caractères suivants : " ", ".", "(", ")"';
                }
                if ( (!preg_match("/^[a-zA-Z0-9 \.\(\)]+$/", $_POST['review'])) &&  (!empty($_POST['review']))) {
                    $errors['review'] = 'Veuillez remplir le champ "Avis de la boutique" uniquement avec des caractères alphanumériques et les caractères suivants : " ", ".", "(", ")"';
                }

                if (!preg_match("/^[a-z]+$/", $_POST['category'])){
                    $errors['category'] = 'Veuillez remplir le champ "Catégorie" uniquement avec des caractères alphabétiques';
                }
                if (!preg_match("/^[a-z]+$/", $_POST['highlight'])){
                    $errors['highlight'] = 'Veuillez remplir le champ "Mise en avant" uniquement avec des caractères alphabétiques';
                }

                if (!preg_match("/^[0-9]+$/", $_POST['price'])){
                    $errors['price'] = 'Veuillez remplir le champ "Prix" uniquement avec des caractères numériques';
                }

                if (strlen($_POST['name'])>255){
                    $errors['name'] = 'Veuillez remplir le champ "Nom" avec 255 caractères maximum';
                }
                if (strlen($_POST['category'])>255){
                    $errors['category'] = 'Veuillez remplir le champ "Catégorie" avec 255 caractères maximum';
                }
                if (strlen(strval($_POST['price']))>11){
                    $errors['price'] = 'Veuillez remplir le champ "Prix" avec 11 caractères maximum';
                }
                if (strlen($_POST['highlight'])>3){
                    $errors['highlight'] = 'Veuillez remplir le champ "Mise en avant" avec 3 caractères maximum';
                }
                if (strlen($_POST['description'])>5000){
                    $errors['description'] = 'Veuillez remplir le champ "Description" avec 5000 caractères maximum';
                }
                if ((strlen($_POST['review'])>5000) && (!empty($_POST['review']))){
                    $errors['review'] = 'Veuillez remplir le champ "Avis de la boutique" avec 5000 caractères maximum';
                }

                if (!empty($_FILES['picture']['name'])) {
                    $extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
                    if (($extension != "png") && ($extension != "jpg")) {
                        $errors['picture'] = 'Veuillez télécharger une image au format jpg ou png uniquement';
                    }
                }

                if(0==count($errors)) {
                    $itemManager = new ItemManager($this->getPdo());

                    $item = new Item();
                    $item->setName($_POST['name']);
                    $item->setCategory($_POST['category']);
                    $item->setPrice($_POST['price']);

                    if (!empty($_FILES['picture']['name'])) {
                        $extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' .$extension;
                        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
                        $uploadFile = $uploadDir . $filename;
                        move_uploaded_file($_FILES['picture']['tmp_name'], $uploadFile);
                        $item->setPicture($uploadFile);
                    }

                    $item->setDescription($_POST['description']);
                    $item->setReview($_POST['review']);

                    $item->setHighlight($_POST['highlight']);
                    if (!empty($_POST['highlight'])) {
                        $item->setHighlight(1);
                    } else {
                        $item->setHighlight(0);
                    }

                    $id = $itemManager->insert($item);
                    header('Location:/item/' . $id);
                }
            }
        }

        return $this->twig->render('Item/add.html.twig', ['errors' => $errors, 'placeholders' => $_POST
        ]);
    }


    /**
     * Handle item deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $itemManager = new ItemManager($this->getPdo());
        $itemManager->delete($id);
        header('Location:/');
    }
}
