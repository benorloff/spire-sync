import React, { useState, useEffect } from 'react';
import { Button, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

type Operator = 'equals' | 'not_equals';

interface Condition {
    key: string;
    value: string;
    operator: Operator;
}

interface FieldValues {
    warehouses: string[];
}

interface ApiResponse {
    success: boolean;
    data: any;
    error?: string;
}

const STATUS_OPTIONS = [
    { value: '0', label: __('Active', 'spire-sync') },
    { value: '1', label: __('On Hold', 'spire-sync') },
    { value: '2', label: __('Inactive', 'spire-sync') }
];

const Settings: React.FC = () => {
    const [conditions, setConditions] = useState<Condition[]>([]);
    const [fieldValues, setFieldValues] = useState<FieldValues>({
        warehouses: []
    });
    const [isLoadingValues, setIsLoadingValues] = useState(false);

    const fetchFieldValues = async () => {
        setIsLoadingValues(true);
        try {
            const warehousesRes = await apiFetch<ApiResponse>({ 
                path: '/spire_sync/v1/inventory/warehouses' 
            });

            setFieldValues({
                warehouses: warehousesRes.data || []
            });
        } catch (error) {
            console.error('Error fetching field values:', error);
        } finally {
            setIsLoadingValues(false);
        }
    };

    useEffect(() => {
        fetchFieldValues();
    }, []);

    const handleConditionChange = (index: number, field: keyof Condition, value: string | Operator) => {
        const newConditions = [...conditions];
        newConditions[index] = {
            ...newConditions[index],
            [field]: value
        };
        setConditions(newConditions);
    };

    const getFieldOptions = (key: string): { label: string; value: string }[] => {
        switch (key) {
            case 'whse':
                return fieldValues.warehouses.map(value => ({ label: value, value }));
            case 'status':
                return STATUS_OPTIONS;
            default:
                return [];
        }
    };

    const getAvailableKeys = (): { label: string; value: string }[] => {
        return [
            { label: __('Warehouse', 'spire-sync'), value: 'whse' },
            { label: __('Status', 'spire-sync'), value: 'status' }
        ];
    };

    return (
        <div className="spire-sync-settings">
            <div className="spire-sync-section">
                <h2>{__('Inventory Sync Settings', 'spire-sync')}</h2>
                <div className="spire-sync-section-header">
                    <Button
                        isPrimary
                        onClick={fetchFieldValues}
                        disabled={isLoadingValues}
                    >
                        {isLoadingValues ? __('Refreshing...', 'spire-sync') : __('Refresh Field Values', 'spire-sync')}
                    </Button>
                </div>
            </div>

            {conditions.map((condition, index) => (
                <div key={index} className="spire-sync-condition">
                    <SelectControl
                        label={__('Field', 'spire-sync')}
                        value={condition.key}
                        options={getAvailableKeys()}
                        onChange={(value) => handleConditionChange(index, 'key', value)}
                    />
                    <SelectControl
                        label={__('Operator', 'spire-sync')}
                        value={condition.operator}
                        options={[
                            { label: __('Equals', 'spire-sync'), value: 'equals' },
                            { label: __('Not Equals', 'spire-sync'), value: 'not_equals' }
                        ]}
                        onChange={(value) => handleConditionChange(index, 'operator', value as Operator)}
                    />
                    <SelectControl
                        label={__('Value', 'spire-sync')}
                        value={condition.value}
                        options={getFieldOptions(condition.key)}
                        onChange={(value) => handleConditionChange(index, 'value', value)}
                    />
                    <Button
                        isDestructive
                        onClick={() => {
                            const newConditions = [...conditions];
                            newConditions.splice(index, 1);
                            setConditions(newConditions);
                        }}
                    >
                        {__('Remove', 'spire-sync')}
                    </Button>
                </div>
            ))}

            <Button
                isPrimary
                onClick={() => setConditions([...conditions, { key: 'whse', value: '', operator: 'equals' }])}
            >
                {__('Add Condition', 'spire-sync')}
            </Button>
        </div>
    );
};

export default Settings; 