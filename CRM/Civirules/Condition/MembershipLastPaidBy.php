<?php
class CRM_Civirules_Condition_MembershipLastPaidBy extends CRM_CivirulesConditions_Contribution_PaidBy {

  // Keep own copy of private property of parent.
  private $_conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {

    parent::setRuleConditionData($ruleCondition);
    $this->_conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  protected function getLastMembershipPayment($membership) {

    $result = civicrm_api3('MembershipPayment', 'get', [
      'sequential' => 1,
      'membership_id' => $membership['id'],
      'options' => ['sort' => "id desc"],
      'api.Contribution.getsingle' => ['id' => "\$value.contribution_id"],
    ]);
    if (!empty($result['values'])) {
      foreach ($result['values'] as $payment) {
        if (!empty($payment['api.Contribution.getsingle'])) {
          return $payment['api.Contribution.getsingle'];
        }
      }
    }
  }

  /**
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $membership = $triggerData->getEntityData('Membership');
    $contribution = $this->getLastMembershipPayment($membership);

    if (empty($contribution['id'])) {
      return FALSE;
    }
    // Set the Contribution possibly for email token actions.
    $triggerData->setEntityData('Contribution', $contribution);
    $paymentInstrumentIds = explode(',', $this->_conditionParams['payment_instrument_id']);
    switch ($this->_conditionParams['operator']) {
      case 0:
        if (in_array($contribution['payment_instrument_id'], $paymentInstrumentIds)) {
          $isConditionValid = TRUE;
        }
      break;
      case 1:
        if (!in_array($contribution['payment_instrument_id'], $paymentInstrumentIds)) {
          $isConditionValid = TRUE;
        }
      break;
    }
    return $isConditionValid;
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    // We don't work with triggers that provides Contribution since that can be used with CRM_CivirulesConditions_Contribution_PaidBy.
    return $trigger->doesProvideEntity('Membership');
  }

}