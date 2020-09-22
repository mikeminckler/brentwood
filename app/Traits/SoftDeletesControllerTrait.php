<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait SoftDeletesControllerTrait
{
    abstract protected function getModel();

    public function remove($id)
    {
        $model = $this->getModel()::findOrFail($id);

        if (!auth()->check()) {
            return abort(401);
        }

        if (!auth()->user()->can('delete', $model)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to remove that model'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to remove that model']);
        }

        $model->delete();

        $model_name = Str::title(str_replace('-', ' ', Str::kebab(class_basename($model))));

        return response()->json(['success' => $model_name.' Removed']);
    }

    public function restore($id)
    {
        $model = $this->getModel()::onlyTrashed()
            ->where('id', $id)
            ->first();

        if (!auth()->check()) {
            return abort(401);
        }

        if (!auth()->user()->can('delete', $model)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to restore that model'], 403);
            }
            return redirect('/')->with(['error' => 'You do not have permission to restore that model']);
        }

        $model->restore();

        $model_name = Str::title(str_replace('-', ' ', Str::kebab(class_basename($model))));

        return response()->json(['success' => $model_name.' Restored']);
    }
}
