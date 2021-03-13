<?php

/**
 * Class for CiviRules post trigger handling payment.
 *
 * @license AGPL-3.0
 */
class CRM_CivirulesPostTrigger_EntityPaymentBase extends CRM_Civirules_Trigger_Post {

  /**
   * To prevent rules being triggered multiple times, we keep a
   * list of  rule ids  and entity ids already triggered.
   */
  static protected $triggeredRules = [];

  /**
   * Return an array of Entity DAO classes for additional entities, keyed by
   * Entity name.
   *
   * This is used to create Entity Definitions.
   * Sub-classes should override.
   *
   * @see CRM_CivirulesPostTrigger_EntityPaymentBase::getAdditionalEntities()
   */
  protected function getAdditionalEntityClasses() {
    return [
      'Contribution' => 'CRM_Contribution_DAO_Contribution',
    ];
  }

  /**
   * Returns an array of entities on which the trigger reacts.
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), $this->objectName);
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   * SubClasses should override.
   *
   * @return string
   */
  protected function getDaoClassName() {
    return '';
  }

  /**
   * Return definition of additional entities.
   *
   * Sub classes should override.
   */
  public function getAdditionalEntities() {
    $entity_classes = $this->getAdditionalEntityClasses();
    foreach ($entity_classes as $name => $class) {
      $entity_definitions[] = new CRM_Civirules_TriggerData_EntityDefinition($name, $name, $class);
    }
    return $entity_definitions;

  }

  /**
   *
   */
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

  /**
   *
   */
  protected function crm($entity, $op, $params) {
    try {
      $result = civicrm_api3($entity, $op, $params);
      return $result;
    }
    catch (Exception $e) {
    }
  }

  /**
   * This is mentioned in the docs but is not invoked anywhere?
   *
   * @see https://docs.civicrm.org/civirules/en/latest/trigger/
   */
  public function checkTrigger() {

  }

  /**
   * Check whether a rule has already been triggered with the object in this request.
   * This can be used to avoid duplicate triggers from multiple post hook invocations.
   */
  public function hasAlreadyTriggered($objectId) {
    $ruleId = $this->getRuleId();
    if (empty(self::$triggeredRules[$ruleId][$objectId])) {
      self::$triggeredRules[$ruleId][$objectId] = 1;
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Trigger a rule for this trigger.
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef) {
    if (!$this->hasAlreadyTriggered($objectId)) {
      return parent::triggerTrigger($op, $objectName, $objectId, $objectRef);
    }
  }

}
