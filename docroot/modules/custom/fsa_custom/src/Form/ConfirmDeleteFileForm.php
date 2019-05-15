<?php
/**
 * Created by PhpStorm.
 * User: benjamindudiak-fry
 * Date: 2019-05-13
 * Time: 16:07
 */

namespace Drupal\fsa_custom\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Form\ConfirmFormHelper;

class ConfirmDeleteFileForm extends ConfirmFormBase {

  protected $file_entity;

  const FILE_LIST_VIEW_ROUTE = 'view.files.page_1';

  public function buildForm(array $form, FormStateInterface $form_state, $fid = NULL) {
    $this->file_entity = File::load($fid);

    if (empty($this->file_entity)) {
      return $this->buildErrorForm($fid);
    }
    else {
      return parent::buildForm($form, $form_state);
    }
  }

  public function buildErrorForm($fid) {
    $form['#title'] = t("Cannot delete file");

    $form['#attributes']['class'][] = 'confirmation';
    $form[$this->getFormName()] = ['#type' => 'hidden', '#value' => 1];
    $form['description']['#markup'] = t("Unable to load data for fid %fid.", ['%fid' => $fid]);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['cancel'] = ConfirmFormHelper::buildCancelLink($this, $this->getRequest());
    if (!isset($form['#theme'])) {
      $form['#theme'] = 'confirm_form';
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect(ConfirmDeleteFileForm::FILE_LIST_VIEW_ROUTE);
    if (isset($this->file_entity)) {
      if (file_exists($this->file_entity->getFileUri())) {
        $this->file_entity->delete();
      }
      else {
        \Drupal::logger('file system')
          ->error('Could not delete file "%path", file does not exist', ['%path' => $this->file_entity->getFileUri()]);
      }
    }
    else {
      \Drupal::logger('file system')
        ->error('Could not delete file, unable to load file information from id');
    }

  }

  public function getFormId() {
    return "confirm_delete_file_form";
  }

  public function getCancelUrl() {
    return Url::fromRoute(ConfirmDeleteFileForm::FILE_LIST_VIEW_ROUTE);
  }

  public function getQuestion() {
    return t("Are you sure you want to delete the file %filename?", ['%filename' => $this->file_entity->getFilename()]);
  }

  public function getDescription() {

    $usages = \Drupal::service('file.usage')->listUsage($this->file_entity);
    $usage_count = array_reduce($usages, function ($acc, $v) {
      $acc += array_reduce($v, function ($acc, $v) {
        $acc += array_sum($v);
        return $acc;
      });
      return $acc;
    }, 0);

    return t('You are about to delete file %filename. This file is used in @usage_count places. This action cannot be undone.', [
      '%filename' => $this->file_entity->getFilename(),
      '@usage_count' => $usage_count,
    ]);
  }

}