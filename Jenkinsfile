pipeline {
    agent any
    options {
        disableConcurrentBuilds()
        timeout(time: 30, unit: 'MINUTES')
    }

    parameters {
        booleanParam(
            name: 'DEPLOY_TO_PRODUCTION',
            defaultValue: false,
            description: '¡CUIDADO! Marcar para desplegar a producción'
        )
    }

    environment {
        CHOCAPP_CREDS = credentials('chocapp')

        // Staging
        STG_CONTAINER_PREFIX = 'chocapp_staging'
        STG_EXPOSED_PORT     = '9092'
        STG_DOMAIN           = 'stg.chocapp.reddantechnology.com'
        STG_VHOST_CONF       = 'stg.chocapp.reddantechnology.com.conf'
        STG_DIR_DESTINY      = '/var/www/chocapp/staging/stg.chocapp.reddantechnology.com'

        // Production
        PROD_CONTAINER_PREFIX = 'chocapp_production'
        PROD_EXPOSED_PORT     = '9091'
        PROD_DOMAIN           = 'chocapp.reddantechnology.com'
        PROD_VHOST_CONF       = 'chocapp.reddantechnology.com.conf'
        PROD_DIR_DESTINY      = '/var/www/chocapp/production/chocapp.reddantechnology.com'

        NGINX_SITES_AVAILABLE = '/etc/nginx/sites-available'
        NGINX_SITES_ENABLED   = '/etc/nginx/sites-enabled'
    }

    stages {

        stage('Load credentials') {
            steps {
                script {
                    echo "Branch: ${env.BRANCH_NAME} | Workspace: ${env.WORKSPACE}"
                    def raw = sh(
                        script: "grep -v '^[[:space:]]*#' \"${CHOCAPP_CREDS}\" | grep -v '^[[:space:]]*\$'",
                        returnStdout: true
                    ).trim()
                    raw.split('\n').each { line ->
                        def idx = line.indexOf('=')
                        if (idx > 0) {
                            env."${line.substring(0, idx).trim()}" = line.substring(idx + 1).trim()
                        }
                    }
                    echo "Credenciales cargadas."
                }
            }
        }

        stage('Security scan') {
            steps {
                script {
                    sh 'php -l public/index.php || true'
                    sh 'composer audit --no-interaction || true'
                }
            }
        }

        stage('ENV - staging') {
            when { branch 'staging' }
            steps {
                sh 'cp .env.example .env'
                script {
                    def props = [
                        'APP_KEY'              : env.STG_APP_KEY,
                        'DB_CONNECTION'        : env.STG_DB_CONNECTION,
                        'DB_HOST'              : env.STG_DB_HOST,
                        'DB_PORT'              : env.STG_DB_PORT,
                        'DB_DATABASE'          : env.STG_DB_DATABASE,
                        'DB_USERNAME'          : env.STG_DB_USERNAME,
                        'DB_PASSWORD'          : env.STG_DB_PASSWORD,
                        'DB_ROOT_PASSWORD'     : env.STG_DB_ROOT_PASSWORD,
                        'REDIS_PASSWORD'       : env.STG_REDIS_PASSWORD,
                        'AWS_ACCESS_KEY_ID'    : env.AWS_ACCESS_KEY_ID,
                        'AWS_SECRET_ACCESS_KEY': env.AWS_SECRET_ACCESS_KEY,
                        'AWS_DEFAULT_REGION'   : env.AWS_DEFAULT_REGION,
                        'AWS_BUCKET'           : env.STG_AWS_BUCKET,
                        'FCM_PROJECT_ID'       : env.FCM_PROJECT_ID,
                        'FCM_SERVER_KEY'       : env.FCM_SERVER_KEY,
                        'MAIL_HOST'            : env.MAIL_HOST,
                        'MAIL_PORT'            : env.MAIL_PORT,
                        'MAIL_USERNAME'        : env.MAIL_USERNAME,
                        'MAIL_PASSWORD'        : env.MAIL_PASSWORD,
                        'MAIL_ENCRYPTION'      : env.MAIL_ENCRYPTION,
                        'MAIL_FROM_ADDRESS'    : env.MAIL_FROM_ADDRESS,
                    ]
                    def content = readFile('.env')
                    props.each { k, v ->
                        content = content.replaceAll(/(^|\n)${k}=.*/, "\$1${k}=${v}")
                    }
                    writeFile(file: '.env', text: content)
                }
            }
        }

        stage('ENV - production') {
            when { branch 'master' }
            steps {
                sh 'cp .env.example .env'
                script {
                    def props = [
                        'APP_KEY'              : env.PROD_APP_KEY,
                        'DB_CONNECTION'        : env.PROD_DB_CONNECTION,
                        'DB_HOST'              : env.PROD_DB_HOST,
                        'DB_PORT'              : env.PROD_DB_PORT,
                        'DB_DATABASE'          : env.PROD_DB_DATABASE,
                        'DB_USERNAME'          : env.PROD_DB_USERNAME,
                        'DB_PASSWORD'          : env.PROD_DB_PASSWORD,
                        'DB_ROOT_PASSWORD'     : env.PROD_DB_ROOT_PASSWORD,
                        'REDIS_PASSWORD'       : env.PROD_REDIS_PASSWORD,
                        'AWS_ACCESS_KEY_ID'    : env.AWS_ACCESS_KEY_ID,
                        'AWS_SECRET_ACCESS_KEY': env.AWS_SECRET_ACCESS_KEY,
                        'AWS_DEFAULT_REGION'   : env.AWS_DEFAULT_REGION,
                        'AWS_BUCKET'           : env.PROD_AWS_BUCKET,
                        'FCM_PROJECT_ID'       : env.FCM_PROJECT_ID,
                        'FCM_SERVER_KEY'       : env.FCM_SERVER_KEY,
                        'MAIL_HOST'            : env.MAIL_HOST,
                        'MAIL_PORT'            : env.MAIL_PORT,
                        'MAIL_USERNAME'        : env.MAIL_USERNAME,
                        'MAIL_PASSWORD'        : env.MAIL_PASSWORD,
                        'MAIL_ENCRYPTION'      : env.MAIL_ENCRYPTION,
                        'MAIL_FROM_ADDRESS'    : env.MAIL_FROM_ADDRESS,
                    ]
                    def content = readFile('.env')
                    props.each { k, v ->
                        content = content.replaceAll(/(^|\n)${k}=.*/, "\$1${k}=${v}")
                    }
                    writeFile(file: '.env', text: content)
                }
            }
        }

        stage('Port validation - staging') {
            when { branch 'staging' }
            steps {
                script {
                    def port   = env.STG_EXPOSED_PORT
                    def prefix = env.STG_CONTAINER_PREFIX
                    def inUse  = sh(script: "ss -tuln | grep -c ':${port} ' || true", returnStdout: true).trim().toInteger()
                    if (inUse > 0) {
                        def own = sh(script: "docker ps --filter 'publish=${port}' --filter 'name=${prefix}' -q | wc -l", returnStdout: true).trim().toInteger()
                        if (own == 0) { error("Puerto ${port} ocupado por otro proceso.") }
                        echo "Puerto ${port} en uso por contenedores propios — serán reemplazados."
                    } else {
                        echo "Puerto ${port} disponible."
                    }
                }
            }
        }

        stage('Port validation - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                script {
                    def port   = env.PROD_EXPOSED_PORT
                    def prefix = env.PROD_CONTAINER_PREFIX
                    def inUse  = sh(script: "ss -tuln | grep -c ':${port} ' || true", returnStdout: true).trim().toInteger()
                    if (inUse > 0) {
                        def own = sh(script: "docker ps --filter 'publish=${port}' --filter 'name=${prefix}' -q | wc -l", returnStdout: true).trim().toInteger()
                        if (own == 0) { error("Puerto ${port} ocupado por otro proceso.") }
                        echo "Puerto ${port} en uso por contenedores propios — serán reemplazados."
                    } else {
                        echo "Puerto ${port} disponible."
                    }
                }
            }
        }

        stage('Virtualhost setup - staging') {
            when { branch 'staging' }
            steps {
                script {
                    def vhostFile    = "${env.NGINX_SITES_AVAILABLE}/${env.STG_VHOST_CONF}"
                    def vhostEnabled = "${env.NGINX_SITES_ENABLED}/${env.STG_VHOST_CONF}"
                    if (sh(script: "[ -f '${vhostFile}' ] && echo yes || echo no", returnStdout: true).trim() == 'no') {
                        def cfg = """server {
    listen 80;
    server_name ${env.STG_DOMAIN};
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    location / {
        proxy_pass         http://127.0.0.1:${env.STG_EXPOSED_PORT};
        proxy_http_version 1.1;
        proxy_set_header   Host              \$host;
        proxy_set_header   X-Real-IP         \$remote_addr;
        proxy_set_header   X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_connect_timeout 300;
        proxy_send_timeout    300;
        proxy_read_timeout    300;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}"""
                        writeFile(file: '/tmp/stg_chocapp_vhost.conf', text: cfg)
                        sh "sudo cp /tmp/stg_chocapp_vhost.conf ${vhostFile}"
                        sh "sudo ln -sf ${vhostFile} ${vhostEnabled}"
                        sh "sudo nginx -t && sudo systemctl reload nginx"
                        echo "Virtualhost ${env.STG_DOMAIN} creado."
                    } else {
                        echo "Virtualhost ya existe: ${vhostFile}"
                    }
                }
            }
        }

        stage('Virtualhost setup - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                script {
                    def vhostFile    = "${env.NGINX_SITES_AVAILABLE}/${env.PROD_VHOST_CONF}"
                    def vhostEnabled = "${env.NGINX_SITES_ENABLED}/${env.PROD_VHOST_CONF}"
                    if (sh(script: "[ -f '${vhostFile}' ] && echo yes || echo no", returnStdout: true).trim() == 'no') {
                        def cfg = """server {
    listen 80;
    server_name ${env.PROD_DOMAIN};
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    location / {
        proxy_pass         http://127.0.0.1:${env.PROD_EXPOSED_PORT};
        proxy_http_version 1.1;
        proxy_set_header   Host              \$host;
        proxy_set_header   X-Real-IP         \$remote_addr;
        proxy_set_header   X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_connect_timeout 300;
        proxy_send_timeout    300;
        proxy_read_timeout    300;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}"""
                        writeFile(file: '/tmp/prod_chocapp_vhost.conf', text: cfg)
                        sh "sudo cp /tmp/prod_chocapp_vhost.conf ${vhostFile}"
                        sh "sudo ln -sf ${vhostFile} ${vhostEnabled}"
                        sh "sudo nginx -t && sudo systemctl reload nginx"
                        echo "Virtualhost ${env.PROD_DOMAIN} creado."
                    } else {
                        echo "Virtualhost ya existe: ${vhostFile}"
                    }
                }
            }
        }

        stage('Deploy - staging') {
            when { branch 'staging' }
            steps {
                echo 'Desplegando ChocApp en staging...'
                script {
                    sh "mkdir -p ${env.STG_DIR_DESTINY}"
                    sh "cd ${env.STG_DIR_DESTINY} && docker compose --project-name ${env.STG_CONTAINER_PREFIX} down --remove-orphans || true"
                    sh "docker rm -f ${env.STG_CONTAINER_PREFIX}_app ${env.STG_CONTAINER_PREFIX}_nginx ${env.STG_CONTAINER_PREFIX}_db ${env.STG_CONTAINER_PREFIX}_redis ${env.STG_CONTAINER_PREFIX}_queue ${env.STG_CONTAINER_PREFIX}_scheduler 2>/dev/null || true"

                    sh """
                        rsync -aO --no-owner --no-group --delete \\
                            --exclude='.git' \\
                            --exclude='vendor' \\
                            --exclude='storage/' \\
                            --exclude='bootstrap/cache/' \\
                            --exclude='node_modules' \\
                            . ${env.STG_DIR_DESTINY}/
                    """

                    sh "printf 'CONTAINER_PREFIX=${env.STG_CONTAINER_PREFIX}\\nEXPOSED_PORT=${env.STG_EXPOSED_PORT}\\n' > ${env.STG_DIR_DESTINY}/.env"

                    sh """
                        mkdir -p \\
                            ${env.STG_DIR_DESTINY}/storage/logs \\
                            ${env.STG_DIR_DESTINY}/storage/app/public \\
                            ${env.STG_DIR_DESTINY}/storage/framework/cache/data \\
                            ${env.STG_DIR_DESTINY}/storage/framework/sessions \\
                            ${env.STG_DIR_DESTINY}/storage/framework/views \\
                            ${env.STG_DIR_DESTINY}/bootstrap/cache
                    """

                    sh "cd ${env.STG_DIR_DESTINY} && docker compose --project-name ${env.STG_CONTAINER_PREFIX} up --build -d"
                    sh "timeout 120 sh -c 'until docker exec ${env.STG_CONTAINER_PREFIX}_app php -v > /dev/null 2>&1; do sleep 3; done'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan key:generate --force'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan migrate:fresh --seed --force'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize:clear'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan l5-swagger:generate'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true"

                    echo "Staging: http://${env.STG_DOMAIN} (puerto ${env.STG_EXPOSED_PORT})"
                    echo "Swagger: http://${env.STG_DOMAIN}/api/documentation"
                }
            }
        }

        stage('Deploy - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                echo 'Desplegando ChocApp en producción...'
                script {
                    sh "mkdir -p ${env.PROD_DIR_DESTINY}"
                    sh "cd ${env.PROD_DIR_DESTINY} && docker compose --project-name ${env.PROD_CONTAINER_PREFIX} down --remove-orphans || true"
                    sh "docker rm -f ${env.PROD_CONTAINER_PREFIX}_app ${env.PROD_CONTAINER_PREFIX}_nginx ${env.PROD_CONTAINER_PREFIX}_db ${env.PROD_CONTAINER_PREFIX}_redis ${env.PROD_CONTAINER_PREFIX}_queue ${env.PROD_CONTAINER_PREFIX}_scheduler 2>/dev/null || true"

                    sh """
                        rsync -aO --no-owner --no-group --delete \\
                            --exclude='.git' \\
                            --exclude='vendor' \\
                            --exclude='storage/' \\
                            --exclude='bootstrap/cache/' \\
                            --exclude='node_modules' \\
                            . ${env.PROD_DIR_DESTINY}/
                    """

                    sh "printf 'CONTAINER_PREFIX=${env.PROD_CONTAINER_PREFIX}\\nEXPOSED_PORT=${env.PROD_EXPOSED_PORT}\\n' > ${env.PROD_DIR_DESTINY}/.env"

                    sh """
                        mkdir -p \\
                            ${env.PROD_DIR_DESTINY}/storage/logs \\
                            ${env.PROD_DIR_DESTINY}/storage/app/public \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/cache/data \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/sessions \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/views \\
                            ${env.PROD_DIR_DESTINY}/bootstrap/cache
                    """

                    sh "cd ${env.PROD_DIR_DESTINY} && docker compose --project-name ${env.PROD_CONTAINER_PREFIX} up --build -d"
                    sh "timeout 120 sh -c 'until docker exec ${env.PROD_CONTAINER_PREFIX}_app php -v > /dev/null 2>&1; do sleep 3; done'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader --no-dev"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan key:generate --force'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan migrate --force'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan config:cache && php artisan route:cache && php artisan view:cache'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true"

                    echo "Producción desplegada en https://${env.PROD_DOMAIN} (puerto ${env.PROD_EXPOSED_PORT})"
                }
            }
        }
    }

    post {
        always {
            node('any') {
                script {
                    try { deleteDir() } catch (Exception e) { echo "Cleanup warning: ${e.message}" }
                }
            }
        }
        success {
            echo "Pipeline ChocApp finalizado con exito — Rama: ${env.BRANCH_NAME}"
        }
        failure {
            echo "Pipeline ChocApp FALLIDO — Rama: ${env.BRANCH_NAME}. Revisa los logs."
        }
    }
}
