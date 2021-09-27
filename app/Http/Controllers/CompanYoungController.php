<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonsData;
use App\Models\PersonsUniqueField;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class CompanYoungController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        $uniqueFields = $this->getAllUniqueFields();

        if (count($uniqueFields) === 0) {
            return view(
                'index',
                [
                    'mainMessage' => 'There are no unique fields at the moment.',
                ]
            );
        }

        if ($personIds = $request->input('person_ids')) {
            $persons = Person::query()
                ->whereIn('id', explode(',', $personIds))
                ->get();
        } else {
            $persons = Person::all();
        }

        $personsDataConstruct = $this->getResults($persons);

        if (
            ($sortField = $request->input('sort_by_field')) &&
            ($sortOrder = $request->input('sort_by_order'))
        ) {
            $personsDataConstruct = $this->sortResultSet($personsDataConstruct, $sortField, $sortOrder);
        }

        return view(
            'index',
            [
                'mainMessage' =>
                    sprintf(
                        'Currently there are %d unique fields and %d persons altogether.',
                        count($uniqueFields),
                        count($personsDataConstruct)
                    ),
                'tableHeaders' => $uniqueFields,
                'personsDataConstruct' => $personsDataConstruct,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function addUniqueField(Request $request): Application|Factory|View
    {
        return view(
            'uniqueField'
        );
    }

    /**
     * @param Request $request
     * @return View|Factory|Redirector|RedirectResponse|Application
     */
    public function saveUniqueField(Request $request): View|Factory|Redirector|RedirectResponse|Application
    {
        $fieldAlias = $request->input('uniqueFieldAlias');
        $fieldName = $request->input('uniqueFieldName');

        if (PersonsUniqueField::where('alias', $fieldAlias)->first()) {
            return view(
                'uniqueField',
                [
                    'errorMessage' => 'Field already exists'
                ]
            );
        }

        $field = new PersonsUniqueField();
        $field->alias = $fieldAlias;
        $field->name = $fieldName;
        $field->save();

        return redirect('/');
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function addPerson(Request $request): Factory|View|Application
    {
        $fields = $this->getAllUniqueFields();

        return view(
            'addPerson',
            [
                'uniqueFields' => $fields,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    public function savePerson(Request $request): Redirector|Application|RedirectResponse
    {
        $fields = $this->getAllUniqueFields();
        $insertData = [];

        foreach ($fields as $field) {
            if ($value = $request->input($field['alias'])) {
                $uniqueFieldId = PersonsUniqueField::where('alias', $field['alias'])->first()->id;

                $insertData[$uniqueFieldId] = $value;
            }
        }

        if ($insertData) {
            $person = new Person();
            $person->save();

            foreach ($insertData as $key => $value) {
                $personData = new PersonsData();
                $personData->field_id = $key;
                $personData->person_id = $person->id;
                $personData->value = $value;
                $personData->save();
            }
        }

        return redirect('/');
    }

    /**
     * @param Request $request
     * @return View|Factory|Redirector|RedirectResponse|Application
     */
    public function search(Request $request): View|Factory|Redirector|RedirectResponse|Application
    {
        $fieldId = $request->input('dropdownSearch');
        $searchValue = $request->input('searchValue');

        if (!$searchValue) {
            return redirect('/');
        }

        if ($fieldId === 'all') {
            $results = PersonsData::query()
                ->where('value', 'LIKE', '%' . $searchValue . '%')
                ->pluck('person_id')
                ->toArray();
        } else {
            $results = PersonsData::query()
                ->where('field_id', '=', $fieldId)
                ->where('value', 'LIKE', '%' . $searchValue . '%')
                ->pluck('person_id')
                ->toArray();
        }

        return redirect('/?person_ids=' . implode(',', $results));
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function sort(Request $request): Application|RedirectResponse|Redirector
    {
        $sortField = $request->input('dropdownSortField');
        $sortOrder = $request->input('dropdownSortOrder');

        return redirect('/?sort_by_field=' . $sortField . '&sort_by_order=' . $sortOrder);
    }

    /**
     * @param Collection $persons
     * @return array
     */
    private function getResults(Collection $persons): array
    {
        $uniqueFieldAliases = PersonsUniqueField::all()->pluck('alias', 'id')->toArray();
        $personsDataConstruct = [];

        foreach ($persons as $person) {
            $personData = PersonsData::where('person_id', $person->id)->get();

            foreach ($personData as $data) {
                $personsDataConstruct[$person->id][$uniqueFieldAliases[$data->field_id]] = $data->value;
            }

            $tmp = [];

            foreach ($uniqueFieldAliases as $alias) {
                if (!isset($personsDataConstruct[$person->id][$alias])) {
                    $tmp[$alias] = '';
                } else {
                    $tmp[$alias] = $personsDataConstruct[$person->id][$alias];
                }
            }

            $personsDataConstruct[$person->id] = $tmp;
        }

        return $personsDataConstruct;
    }

    /**
     * @return array
     */
    private function getAllUniqueFields(): array
    {
        $fields = [];

        PersonsUniqueField::all()
            ->each(static function (PersonsUniqueField $field) use (&$fields) {
                $fields[] = [
                    'id' => $field->id,
                    'alias' => $field->alias,
                    'name' => $field->name,
                ];
            });

        return $fields;
    }

    /**
     * @param array $data
     * @param string $field
     * @param string $order
     * @return array
     */
    private function sortResultSet(array $data, string $field, string $order): array
    {
        $fieldArray = [];

        foreach ($data as $key => $value)
        {
            $fieldArray[$key] = $value[$field];
        }

        if ($order === 'asc') {
            array_multisort($fieldArray, SORT_ASC, $data);
        } else {
            array_multisort($fieldArray, SORT_DESC, $data);
        }

        return $data;
    }
}
