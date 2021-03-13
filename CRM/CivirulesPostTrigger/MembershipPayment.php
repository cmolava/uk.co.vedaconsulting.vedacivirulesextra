<?php

use CRM_Vedacivirulesextra_ExtensionUtil as E;

/**
 * Class for CiviRules post trigger handling - MembershipPayment
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_MembershipPayment extends CRM_CivirulesPostTrigger_EntityPaymentBase {
  /**
   * Return an array of Entity DAO classes for additional entities, keyed by
   * Entity name.
   *
   * This is used to create Entity Definitions.
   * Sub-classes should override.
   *
   * @see CRM_CivirulesPostTrigger_EntityPaymentBase::getAdditionalEntities().
   */
  protected function getAdditionalEntityClasses() {
    return [
      'Contribution' => 'CRM_Contribution_DAO_Contribution',
      'Membership' => 'CRM_Member_DAO_Membership',
    ];
  }


  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Member_DAO_MembershipPayment';
  }

  public function getTriggerDescription() {
    return E::ts('Provides Membership and Contribution for Conditions and Actions.');
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
  }
}
