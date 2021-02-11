<?php
class CRM_Civirules_Condition_MembershipHasStatusOverride extends CRM_Civirules_Condition {

  /**
   * Determines if the condition is valid.
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $membership = $triggerData->getEntityData('Membership');
    $isConditionValid = !empty($membership['is_override']);
    CRM_Core_Error::debug_var(__CLASS__, [$membership, $isConditionValid]);
    return $isConditionValid;
  }

  /**
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return $trigger->doesProvideEntity('Membership');
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return FALSE;
  }

}