<?php

test('admin login page returns a successful response', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
});

test('employee login page returns a successful response', function () {
    $response = $this->get('/employee/login');

    $response->assertStatus(200);
});
