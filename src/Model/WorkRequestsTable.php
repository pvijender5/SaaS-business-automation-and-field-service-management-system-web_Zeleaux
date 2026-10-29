<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WorkRequests Model
 *
 * @property \App\Model\Table\WorkRequestsTable|\Cake\ORM\Association\BelongsTo $ParentWorkRequests
 * @property \App\Model\Table\CompaniesTable|\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\EmployeesTable|\Cake\ORM\Association\BelongsTo $Employees
 * @property \App\Model\Table\ContactsTable|\Cake\ORM\Association\BelongsTo $Contacts
 * @property \App\Model\Table\CustomerContactsTable|\Cake\ORM\Association\BelongsTo $CustomerContacts
 * @property \App\Model\Table\SubmitByContactsTable|\Cake\ORM\Association\BelongsTo $SubmitByContacts
 * @property \App\Model\Table\WorkRequestsTable|\Cake\ORM\Association\HasMany $ChildWorkRequests
 *
 * @method \App\Model\Entity\WorkRequest get($primaryKey, $options = [])
 * @method \App\Model\Entity\WorkRequest newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\WorkRequest[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WorkRequest|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkRequest|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WorkRequest[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\WorkRequest findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorkRequestsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('work_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ParentWorkRequests', [
            'className' => 'WorkRequests',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Employees', [
            'className' => 'Users',
            'foreignKey' => 'employee_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Contacts', [
            'className' => 'Users',
            'foreignKey' => 'contact_id',
            'joinType' => 'INNER'
        ]);
        $this->hasOne('Quotations', [
            'foreignKey' => 'work_request_id',
        ]);
        $this->belongsTo('Customers', [
            'className' => 'Users',
            'foreignKey' => 'customer_contact_id',
            'joinType' => 'LEFT'
        ]);
        $this->hasMany('WorkRequestAttachments', [
            'foreignKey' => 'work_request_id',
        ]);
        $this->hasMany('WorkRequestsComments', [
            'foreignKey' => 'work_request_id',
        ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('serial_no')
            ->requirePresence('serial_no', 'create')
            ->notEmpty('serial_no');

        $validator
            ->scalar('location_address')
            ->maxLength('location_address', 255)
            ->allowEmpty('location_address');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 255)
            ->allowEmpty('phone');

        $validator
            ->scalar('address_1')
            ->maxLength('address_1', 255)
            ->allowEmpty('address_1');

        $validator
            ->email('email')
            ->allowEmpty('email');

        $validator
            ->date('date_needed_by')
            ->allowEmpty('date_needed_by');

        $validator
            ->allowEmpty('is_it_urgent');

        $validator
            ->scalar('description')
            ->allowEmpty('description');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {


        return $rules;
    }
}
