<?php

class UserCampaign extends DataObject {
    public $__table = 'ce_user_campaign';
    public $id;
    public $userId;
    public $campaignId;
    public $enrollmentDate;
    public $unenerollmentDate;
    public $completed;

    public static function getObjectStructure($context = ''): array {
        return [
            'id' => [
                'property' => 'id',
                'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'userId' => [
                'property' => 'userId',
                'type' => 'label',
				'label' => 'User Id',
				'description' => 'The unique id of the user',
            ],
            'campaignId' => [
                'property' => 'campaignId',
                'type' => 'label',
				'label' => 'Campaign Id',
				'description' => 'The unique id of the campaign',
            ],
            'enrollmentDate' => [
                'property' => 'enrollmentDate',
                'type' => 'date',
				'label' => 'Enrollment Date',
				'description' => 'The Date of Enrollment',
            ],
            'unenrollmentDate' => [
                'property' => 'unenrollmentDate',
                'type' => 'date',
				'label' => 'Unenrollment Date',
				'description' => 'The Date of Unenrollment',
            ],
            'completed' => [
                'property' => 'completed',
                'type' => 'checkbox',
				'label' => 'Enrollment Date',
				'description' => 'The Date of Enrollment',
            ]
        ];
    }

}