<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicines\AlternativeRequest;
use App\Http\Requests\Medicines\MedicineFilterRequest;
use App\Http\Requests\Medicines\MedicineNameRequest;
use App\Http\Requests\Medicines\MedicineStoreRequest;
use App\Http\Requests\Medicines\MedicineUpdateRequest;
use App\Models\Company;
use App\Models\Medicine;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(): JsonResponse
    {
        //
        $this->authorize('viewAny', Medicine::class);
        $medicine = Medicine::all();
        return $this->getJsonResponse($medicine, 'medicines');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MedicineStoreRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(MedicineStoreRequest $request): JsonResponse
    {
        /**
         * @var Medicine $medicine ;
         */
        $this->authorize('create', Medicine::class);
        $data = $request->validated();

        if (isset($data['company_name'])) {
            $company = Company::firstOrCreate(['company_name' => $data['company_name']]);
            $data['company_id'] = $company->id;
        }

        $medicine = Medicine::query()->create($data);

        if (isset($data['shelf_names'])) {
            foreach ($data['shelf_names'] as $shelf_name) {
                $shelf = Shelf::firstOrCreate(['shelf_name' => $shelf_name]);
                $medicine->shelves()->attach($shelf->id);
            }
        }

        $medicine->materials()->attach($data['material_ids']);
        $medicine->users()->attach(auth()->id());
        if (isset($data['alternative_ids'])) {
            $medicine->alternatives()->attach($data['alternative_ids']);
        }
        return $this->getJsonResponse($medicine, 'Medicine Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param Medicine $medicine
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Medicine $medicine): JsonResponse
    {
        //
        $this->authorize('view', $medicine);
        //Gate::forUser(auth('api')->user())->authorize('showMedicine', $medicine);
        return $this->getJsonResponse($medicine, 'Success');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MedicineUpdateRequest $request
     * @param Medicine $medicine
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(MedicineUpdateRequest $request, Medicine $medicine): JsonResponse
    {
        $this->authorize('update', $medicine);
        $data = $request->validated();


        if (isset($data['alternative_ids'])) {
            $medicine->alternatives()->sync($data['alternative_ids']);
        }
        if (isset($data['shelf_names'])) {
            foreach ($data['shelf_names'] as $shelf_name) {
                $shelf = Shelf::firstOrCreate(['shelf_name' => $shelf_name]);
                $medicine->shelves()->attach($shelf->id);
            }
        }
        if (isset($data['material_ids'])) {
            $medicine->materials()->sync($data['material_ids']);
        }
        $medicine->update($data);

        return $this->getJsonResponse($medicine, 'Medicine Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Medicine $medicine
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Medicine $medicine): JsonResponse
    {
        //
        $this->authorize('delete', $medicine);
        //Gate::forUser(auth('api')->user())->authorize('deleteMedicine', $medicine);
        $medicine->delete();
        return $this->getJsonResponse($medicine, 'Medicine Deleted Successfully');
    }


    /**
     * @throws AuthorizationException
     */
    public function pharmacies(Medicine $medicine): JsonResponse
    {
        $this->authorize('materials', $medicine);
        $pharmacies = $medicine->users;
        return $this->getJsonResponse($pharmacies, 'pharmacies');
    }

    /**
     * @throws AuthorizationException
     */
    public function materials(Medicine $medicine): JsonResponse
    {
        $this->authorize('materials', $medicine);
        $materials = $medicine->materials;
        return $this->getJsonResponse($materials, 'materials');

    }

    /**
     * @throws AuthorizationException
     */
    public function shelves(Medicine $medicine): JsonResponse
    {
        $this->authorize('shelves', $medicine);
        $shelves = $medicine->shelves;
        return $this->getJsonResponse($shelves, 'shelves');

    }

    /**
     * @throws AuthorizationException
     */
    public function alternatives(AlternativeRequest $request): JsonResponse
    {
        $this->authorize('alternatives', Medicine::class);
        $data = $request->validated();
        /**
         * @var Medicine $medicine ;
         * @var array $alternatives ;
         * @var array $material_ids ;
         */
        $medicine = Medicine::query()->where('id', $data['medicine_id'])->first();
        $materials = $medicine->materials;
        foreach ($materials as $material) {
            $material_ids[] = $material->id;
        }
        if ($data['number'] == 1) {
            $alternatives = Medicine::query()->whereHas('materials',
                function (Builder $builder1) use ($material_ids) {
                    $builder1->where('material_id', '=', $material_ids);
                })->get();
        } else if ($data['number'] == 2) {
            /**
             * @var Medicine[] $alternatives1 ;
             */

            $mad = Medicine::query()->where(function (Builder $builder) use ($material_ids) {
                foreach ($material_ids as $material_id) {
                    $builder->whereHas('materials', function (Builder $builder) use ($material_id) {
                        $builder->where('material_id', $material_id);
                    });
                }
            })->get();
            foreach ($mad as $item) {
                if (count($item->material_ids) == 2) {
                    $alternatives1[] = $item;
                }
            }
            return self::getJsonResponse($alternatives1, 'alternatives');

        } else {
            $alternatives = Medicine::query()->where(function (Builder $builder) use ($material_ids) {
                foreach ($material_ids as $material_id) {
                    $builder->whereHas('materials', function (Builder $builder) use ($material_ids, $material_id) {
                        $builder->where('material_id', $material_id);
                    });
                }

            })->get();

        }

        return self::getJsonResponse($alternatives, "alternatives");

    }

    /**
     * @throws AuthorizationException
     */
    public
    function expiredMedicines(): JsonResponse
    {
        /**
         * @var Medicine[] $medicines ;
         * @var Medicine $medicine ;
         * @var User $user ;
         * @var array $response
         */
        $this->authorize('viewAny', Medicine::class);

        $user = auth('api')->user();
        $medicines = $user->medicines;
        foreach ($medicines as $medicine) {

            if ($medicine->expiration_date < Date::now()) {
                $response[] = $medicine;
            }
        }

        return $this->getJsonResponse($response, 'Expired Medicines');
    }

    /**
     * @throws AuthorizationException
     */
    public function displayedMedicines(): JsonResponse
    {
        /**
         * @var Medicine[] $medicines ;
         * @var Medicine $medicine ;
         * @var User $user ;
         * @var array $response
         */
        $this->authorize('viewAny', Medicine::class);
        $user = auth('api')->user();
        $medicines = $user->medicines;
        foreach ($medicines as $medicine) {

            if ($medicine->quantity <= 5) {
                $response[] = $medicine;
            }
        }
        return $this->getJsonResponse($response, 'run out medicines');

    }

    /**
     * @throws AuthorizationException
     */


    public function medicineFilter(MedicineFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $data = $request->validated();
        if (isset($data['medicine_name'])) {
            $medicines = Medicine::query()->where('name_' . app()->getLocale(), $data['medicine_name'])->get();
        } elseif (isset($data['material_ids'])) {
            $medicines = Medicine::query()->whereHas('materials',
                fn(Builder $builder) =>$builder->whereIn('material_id',$data['material_ids'])
            )->get();
        } else {
            $medicines = Medicine::query()->whereHas('company',
                fn(Builder $builder) =>$builder->where('company_name',$data['company_name'])
            )->get();
        }

        return self::getJsonResponse($medicines,'medicines');


    }

    /**
     * @throws AuthorizationException
     */
    public function getPharmacies(MedicineNameRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $data = $request->validated();
        $pharmacies = User::type('Pharmacy')->with('medicines')->whereHas('medicines',
            fn(Builder $builder) => $builder->where('name_' . app()->getLocale(), $data['medicine_name'])
        )->get();


        return self::getJsonResponse($pharmacies, 'Pharmacies With Medicine Information');
    }


}
