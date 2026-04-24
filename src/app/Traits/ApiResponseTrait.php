<?php

namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JsonSerializable;
use Throwable;

use function response;

trait ApiResponseTrait
{
    public function respondNotFound(string $message = 'Not Found!', string $key = 'message'): JsonResponse
    {
        return $this->apiResponse(
            [$key => $this->morphMessage($message)],
            Response::HTTP_NOT_FOUND
        );
    }

    public function respondWithSuccess(array|Arrayable|JsonSerializable $contents = []): JsonResponse
    {
        $data = $this->morphToArray($contents);

        return $this->apiResponse($data);
    }

    public function respondOk(string $message = 'OK'): JsonResponse
    {
        return $this->respondWithSuccess(['message' => $message]);
    }

    public function respondUnAuthenticated(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message],
            Response::HTTP_FORBIDDEN
        );
    }

    public function respondError(array|string|Arrayable|JsonSerializable|Throwable $errors = [], int $code = 0): JsonResponse
    {
        if ($errors === []) {
            $errors = ['error' => __('Something went wrong')];
        } elseif (is_string($errors) || $errors instanceof Throwable) {
            $errors = ['error' => $errors];
        } else {
            $errors = $this->morphToArray($errors);
        }

        if ($code === 0) {
            $code = Response::HTTP_BAD_REQUEST;
        }

        return $this->apiResponse(
            ['errors' => $errors],
            $code
        );
    }

    public function respondCreated(array|Arrayable|JsonSerializable|Throwable $data = []): JsonResponse
    {
        return $this->apiResponse(
            $this->morphToArray($data),
            Response::HTTP_CREATED
        );
    }

    public function respondFailedValidation(array|string|Arrayable|JsonSerializable|Throwable $errors = []): JsonResponse
    {
        if ($errors === []) {
            $errors = ['error' => __('Something went wrong')];
        } elseif (is_string($errors) || $errors instanceof Throwable) {
            $errors = ['error' => $errors];
        } else {
            $errors = $this->morphToArray($errors);
        }

        return $this->apiResponse(
            ['errors' => $errors],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function respondNoContent(): Response
    {
        return response()->noContent();
    }

    private function apiResponse(array $data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code, [], JSON_UNESCAPED_UNICODE);
    }

    private function morphToArray(array|Arrayable|JsonSerializable|null $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        return [];
    }

    private function morphMessage(string|Throwable $message): string
    {
        return $message instanceof Throwable ? $message->getMessage() : $message;
    }
}