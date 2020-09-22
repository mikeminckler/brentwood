<?php
  
namespace App\Traits;

use Illuminate\Support\Str;

trait SearchTrait
{
    abstract public function getSearchLabelAttribute();
    abstract public function getSearchFieldsAttribute();

    public function search($terms = null, $append = true)
    {
        if (!$terms) {
            $terms = requestInput('terms');
        }

        if (strlen($terms) < 2 && !is_numeric($terms)) {
            return collect();
        }

        $terms = explode(' ', $terms);

        $results = $this->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                foreach ($this->search_fields as $field) {
                    if (strlen($term) > 1 || is_numeric($term)) {
                        $query->orWhere($field, 'LIKE', '%'.$term.'%');
                    }
                }
            }
        })
        ->get();

        if (requestInput('paginate')) {
            return Paginate::create($results);
        }

        if (requestInput('autocomplete')) {
            return [
                'results' => $results->map(function ($result) {
                    $result = $result->append('search_label')->toArray();
                    $result['selected'] = false;
                    return $result;
                })
            ];
        }

        return $results;
    }
}
