<?php

namespace KirschbaumDevelopment\NovaInlineRelationship\Observers;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class HasManyObserver extends BaseObserver
{
    /**
     * {@inheritdoc}
     */
    public function updating(Model $model, $attribute, $value)
    {
        Log::debug([
            'modelKey' => $model->getKeyName(),
            // 'attribute' => $attribute,
            // 'value' => $value
        ]);

        $model->{$attribute}()
            ->whereNotIn($model->getKeyName(), Arr::pluck($value, 'modelId'))
            ->get()
            ->each
            ->delete();

        for ($i = 0; $i < count($value); $i++) {
            $childModel = $model->{$attribute}()->find($value[$i]['modelId']);

            if (empty($childModel)) {
                $model->{$attribute}()->create($value[$i]['fields']);

                continue;
            }

            $childModel->update($value[$i]['fields']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function created(Model $model, $attribute, $value)
    {
        $model->{$attribute}()->createMany(Arr::pluck($value, 'fields'));
    }
}
