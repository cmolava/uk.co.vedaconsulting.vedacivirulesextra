<?php

use CRM_Vedacivirulesextra_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Vedacivirulesextra_Form_Civirules_Action_MembershipUpdateStatus extends  CRM_CivirulesActions_Form_Form {
  protected function getMembershipStatuses() {
    $result = civicrm_api3('MembershipStatus', 'get', [
      'sequential' => 1,
      'is_active' => 1
     ]);
    $return = [];
    foreach ($result['values'] as $status) {
      $return[$status['id']] = $status['label'];
    }
    return $return;
  }

  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id', $this->ruleActionId);
    $descriptions = [];
    $this->addRadio(
       'update_operation',
       E::ts('Update status using:'),
       [
         'none' => E::ts('No status update'),
        'fixed_value' => E::ts('Set to a fixed value'),
        'calculate' => E::ts('Calculate from the Membership Status rules.'),
       ]
     );
    // add form elements
    $this->add(
      'select', 
       'membership_status',
      'Fixed Status',
      [0 => E::ts('-- Select Status --')] + $this->getMembershipStatuses(),
      FALSE 
    );
    $descriptions['membership_status'] = E::ts("It is not recommended to set the status to a fixed value on rules that process memberships in bulk unless you are sure the conditions will give you the correct result.");
    $this->add(
      'checkbox',
      'unset_status_override',
      E::ts('Unset the Status Override')
    ); 
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('descriptions', $descriptions);
    // parent::buildQuickForm();
  }

  public function postProcess() {
    $data = [];
    foreach ($this->getRenderableElementNames() as $name) {
      $data[$name] = $this->_submitValues[$name];
    }
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

 /**
   * 
   * {@inheritDoc}
   * @see CRM_CivirulesActions_Form_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $params = !empty($this->ruleAction->action_params) ? unserialize($this->ruleAction->action_params) : []; 
    $defaultValues += $params;
    return $defaultValues;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
