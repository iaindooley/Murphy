<?php
    /**
    * @database test_fixture2
    * @tables user,group,user_in_group
    * user_id | username                    | group_id | group_name
    * 1       | iain@workingsoftware.com.au | 2        | Users
    * 2       | iaindooley@gmail.com        | 3        | Staff
    */
    Murphy\Fixture::add(function($data)
    {
        $this->link->query('INSERT INTO `group`(group_id,group_name) VALUES('.(int)$data['group_id'].',\''.mysqli::real_escape_string($data['group_name']).'\')') or die(mysqli_error($this->link));
        $this->link->query('INSERT INTO `user`(user_id,username) VALUES('.(int)$data['user_id'].',\''.mysqli::real_escape_string($data['username']).'\')') or die(mysqli_error($this->link));
        $this->link->query('INSERT INTO user_in_group(user_id,group_id) VALUES('.(int)$data['user_id'].','.(int)$data['group_id'].')') or die(mysqli_error($this->link));
    });
