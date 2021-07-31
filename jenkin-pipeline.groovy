pipeline {
    agent { label 'master' }
    stages {
        stage('Prepare') {
            steps {
                script {
                    if (env.CLEAR_WORKSPACE.toBoolean()) {
                        deleteDir()
                    }
                    if (env.UPDATE_DOCKER.toBoolean() || env.CLEAR_WORKSPACE.toBoolean()) {
                        git credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/khoaiscity/vkids-docker.git'
                    }
                }
            }
        }
        stage('Build') {
            steps {
                script {
                    if(env.BACKEND.toBoolean()) {
                        dir ('backend/mlm-ng-goapi') {
                            git branch: 'test', credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/smartblock/mlm-ng-goapi.git'
                        }
                        dir ('./') {
                            sh 'docker-compose up --no-color --build backend-build'
                            sh 'cp ./backend/go-space/bin/mlm-ng-goapi ./release/backend/go-space/bin'
                        }
                    }
                    if(env.ADMIN.toBoolean()) {
                        dir ('admin/mlm-ng-admin') {
                            git branch: 'test', credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/smartblock/mlm-ng-admin.git'
                        }
                        dir ('./') {
                            sh 'docker-compose up --no-color --build admin-build'
                            sh 'cp ./admin/build/admin.tar.gz ./release/admin'
                        }
                    }
                    if(env.MEMBER.toBoolean()) {
                        dir ('member/mlm-ng') {
                            git branch: 'test', credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/smartblock/mlm-ng.git'
                        }
                        dir ('./') {
                            sh 'docker-compose up --no-color --build member-build'
                            sh 'cp ./member/build/member.tar.gz ./release/member'
                        }
                    }
                    if(env.RESOURCE.toBoolean()) {
                        dir ('resource/mlm-ng') {
                            git branch: 'test', credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/smartblock/mlm-ng.git'
                        }
                        dir ('./') {
                            sh 'docker-compose up --no-color --build resource-build'
                            sh 'cp ./resource/build/resource.tar.gz ./release/resource'
                        }
                    }
                    if(env.VKIDS_LAND.toBoolean()) {
                        dir ('vkidsland/vkidsland') {
                            git branch: 'master', credentialsId: '7bda0671-f744-46a8-ad29-7040aaa72b7a', url: 'https://github.com/khoaiscity/vkidsland.git'
                        }
                        dir ('./') {
                            sh 'docker-compose up --no-color --build vkidsland-build'
                            sh 'cp ./vkidsland/build/vkidsland.tar.gz ./release/vkidsland'
                        }
                    }
                }
            }
        }
        stage('Deploy') {
            steps {
                script {
                    dir ('release') {
                        if(env.BACKEND.toBoolean()) {
                            sh 'sh run.sh backend'
                        }
                        if(env.ADMIN.toBoolean()) {
                            sh 'sh run.sh admin'
                        }
                        if(env.MEMBER.toBoolean()) {
                            sh 'sh run.sh member'
                        }
                        if(env.RESOURCE.toBoolean()) {
                            sh 'sh run.sh resource'
                        }
                        if(env.VKIDS_LAND.toBoolean()) {
                            sh 'sh run.sh vkidsland'
                        }
                    }
                }
            }
        }
        stage('Test') {
            steps {
                echo 'Test'
            }
        }
    }
}