<?php

use CRM_Vedacivirulesextra_ExtensionUtil as E;

/**
 * Class for CiviRules post trigger handling - Membership Renewed
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_MembershipPayment extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'MembershipPayment');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Member_DAO_MembershipPayment';
  }
  public function getAdditionalEntities() {
    $entity_definitions = parent::getAdditionalEntities();
    $definitions = [
    //  ['Membership Payment', 'MembershipPayment', 'CRM_Member_DAO_MembershipPayment' ],
      ['Membership', 'Membership', 'CRM_Member_DAO_Membership' ],
      ['Contribution', 'Contribution', 'CRM_Contribute_DAO_Contribution' ],
    ];
    $entity_definitions = [];
    foreach ($definitions as $definition) {
      $entity_definitions[] = new CRM_Civirules_TriggerData_EntityDefinition($definition[0], $definition[1], $definition[2]);
    }
    return $entity_definitions;
  
  }

  public function getProvidedEntities() {
    return parent::getProvidedEntities();
  }

  public function getTriggerDescription() {
    return E::ts('Provides Membership and Contribution for Conditions and Actions.', ['1' => $this->op]);
  }

  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    $membership_payment = $triggerData->getEntityData('MembershipPayment');
    if ($membership_payment['contribution_id']) {
      $contribution = $this->crm('Contribution', 'getsingle', ['id' => $membership_payment['contribution_id']]);
      $triggerData->setEntityData('Contribution', $contribution);
    } 
    if ($membership_payment['membership_id']) {
      $membership = $this->crm('Membership', 'getsingle', ['id' => $membership_payment['membership_id']]);
      $triggerData->setEntityData('Membership', $membership);
    } 
    CRM_Core_Error::debug_var(__CLASS__ . __FUNCTION__, $triggerData);
  }

  protected function crm($entity, $op, $params) {
    try {
      $result = civicrm_api3($entity, $op, $params);
      return $result;
    }
    catch (Exception $e) {
     //
    }
  }

  /**
   * Trigger a rule for this trigger
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef) {
    return parent::triggerTrigger($op, $objectName, $objectId, $objectRef);
  }

}
