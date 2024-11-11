<?php
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestoneUsersProgress.php';
require_once ROOT_DIR . '/sys/Community/Reward.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
class Milestone extends DataObject {
    public $__table = 'ce_milestone';
    public $id;
    public $name;
    public $milestoneType;
    public $conditionalField;
    public $conditionalValue;
    public $campaignId;
    public $conditionalOperator;

  

    public static function getObjectStructure($context = ''): array {
     
        $structure = [
            'id' => [
                'property' => 'id',
                'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'maxLength' => 50,
				'description' => 'A name for the milestone',
				'required' => true,
			],
            'milestoneType' => [
                'property' => 'milestoneType',
                'type' => 'enum',
                'label' => 'When: ',
                'values' => [
                    'user_checkout' => 'Checkout',
                    'user_hold' => 'Hold',
                    'user_work_review' => 'Rating',
                ],
                'onchange' => 'updateConditionalField(this.value)',
            ],
            'conditionalField' => [
                'property' => 'conditionalField',
                'type' => 'enum',
                'label' => 'Conditional Field: ',
                'values' => [
                    'title_display' => 'Title',
                    'author_display' => 'Author',
                    'subject_facet' => 'Subject',
                    'user_list' => 'List (id)',
                     // 'hold_title' => 'Title',
                    // 'hold_author' => 'Author',
                    // 'list_id' => 'List Name',
                    // 'list_name' => 'List Length',
                    // 'work_id' => 'Reviewed Title',
                    // 'work_author' => 'Reviewed Author',
                ],
                'required' => false,
                // var_dump($groupedWorkDriver->getSolrField('format_category_main')); #Books, eBooks, Audiobooks, Music, Video
                // var_dump($groupedWorkDriver->getSolrField('publisherStr'));
                // var_dump($groupedWorkDriver->getSolrField('title_display'));
                // var_dump($groupedWorkDriver->getSolrField('topic_facet'));
                // var_dump($groupedWorkDriver->getSolrField('placeOfPublication'));
                // var_dump($groupedWorkDriver->getSolrField('publishDate'));
                // var_dump($groupedWorkDriver->getSolrField('owning_library_main'));
                // var_dump($groupedWorkDriver->getSolrField('lc_subject'));
                // var_dump($groupedWorkDriver->getSolrField('subject_facet'));
                // var_dump($groupedWorkDriver->getSolrField('itype_main'));
                // var_dump($groupedWorkDriver->getSolrField('format_main'));
                // var_dump($groupedWorkDriver->getSolrField('language'));
                // var_dump($groupedWorkDriver->getSolrField('auth_author2')); #contributors
                // var_dump($groupedWorkDriver->getSolrField('author_display')); #main author
            ],
            'conditionalOperator' => [
                'property' => 'conditionalOperator',
                'type' => 'enum',
                'label' => 'Conditional Operator',
                'values' => [
                    'equals' => 'Is',
                    'is_not' => 'Is Not',
                    'like' => 'Is Like',
                ],
            ],
            'conditionalValue' => [
                'property' => 'conditionalValue',
                'type' => 'text',
                'label' => 'Conditional Value: ',
                'maxLength' => 100,
                'description' => 'Optional value e.g. Fantasy',
                'required' => false,
            ],
        ];
        return $structure;
    } 

    public static function getConditionalFields() {
        $conditionalFields = [
            'user_checkout' => [
                ['value' => 'title', 'label' => 'Title'],
                ['value' => 'author', 'label' => 'Author'],
            ],
            'user_hold' => [
                ['value' => 'hold_title', 'label' => 'Title'],
                ['value' => 'hold_author', 'label' => 'Author'],
            ],
            'user_list' => [
                ['value' => 'list_id', 'label' => 'List Name'],
                ['value' => 'list_name', 'label' => 'List Length'],
            ],
            'user_work_review' => [
                ['value' => 'work_id', 'label' => 'Reviewed Title'],
                ['value' => 'work_author', 'label' => 'Reviewed Author'],
            ],
        ];
        return $conditionalFields;
    }

    /**
  * @return array
  */
  public static function getMilestoneList(): array {
    $milestone = new Milestone();
    $milestoneList = [];
     
    if ($milestone->find()) {
        while ($milestone->fetch()) {
            $milestoneList[$milestone->id] = $milestone->name;
        }
    }
    return $milestoneList;
  }
}

// $conditionalFields = Milestone::getConditionalFields();
/*?>
<script>
    var conditionalFields = <?php echo json_encode($conditionalFields); ?>

    function updateConditionalField(milestoneType) {
        // Get the dropdown element for conditional fields
        var conditionalFieldDropdown = document.querySelector('[name="conditionalField"]');

        // Clear existing options in the dropdown
        conditionalFieldDropdown.innerHTML = '';

        // Check if milestoneType has conditional fields
        var options = conditionalFields[milestoneType] || [];

        // If no options are available
        if (options.length === 0) {
            var noOption = document.createElement('option');
            noOption.value = '';
            noOption.text = 'No conditional fields available';
            conditionalFieldDropdown.appendChild(noOption);
            return;
        }

        // Populate new options
        options.forEach(function(option) {
            var newOption = document.createElement('option');
            newOption.value = option.value;
            newOption.text = option.label;
            conditionalFieldDropdown.appendChild(newOption);
        });
    }

    // Trigger dropdown update when the page loads or the milestoneType is changed
    document.addEventListener('DOMContentLoaded', function() {
        var milestoneTypeDropdown = document.querySelector('[name="milestoneType"]');

        if(milestoneTypeDropdown) {
            milestoneTypeDropdown.addEventListener('change', function() {
                updateConditionalField(this.value);
            });
            updateConditionalField(milestoneTypeDropdown.value);

        } else {
            console.error("Milestone type dropdown not found.");
        }
    });
 
</script>*/