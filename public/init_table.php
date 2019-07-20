<?php

$conn = new mysqli("localhost", 'root', '', 'xtie');
if($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

$query_subject = "create table xtie_subject (
          subject_id int unsigned auto_increment primary key,
          name char(30) not null,
          user_id int unsigned null,
          expire int unsigned not null,
          new int unsigned not null,
          create_time varchar(30) null,
          update_time varchar(30) null,
          foreign key(user_id) references xtie_user(user_id) on update cascade on delete cascade)
          engine=innodb";
$query_course = "create table xtie_course (
                 course_id int unsigned  auto_increment primary key,
                 name varchar(30) not null,
                 content text not null,
                 statu char(10) not null,
                 subject_id int unsigned null,
                 create_time varchar(30) null,
                 update_time varchar(30) null,
                 rank int unsigned null,
                 expire_time varchar(30) null,
                 foreign key(subject_id) references xtie_subject(subject_id) on update cascade on delete cascade) 
                 engine=innodb";

$query_user = "create table xtie_user (
               user_id int unsigned auto_increment primary key,
               username varchar(30) not null,
               password char(100) not null,
               email char(100) not null)
               engine=innodb";

function create($conn, $query) {
    $res = $conn->query($query);
    if(!$res) {
        var_dump($conn->errno) ;
    } else {
        echo "成功创建";
    }
}
$query = "create database xtie character set utf-8";
create($conn ,$query_subject);
create($conn ,$query_course);

