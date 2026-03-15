{ ... }:

{
  languages.php = {
    enable = true;
    extensions = [ "pdo_pgsql" "pgsql" "mbstring" ];
  };

  processes.web-server = {
    exec = "php -S localhost:8000 -t .";
    cwd = "./src";
    after = [ "devenv:processes:postgres" ];
    watch = {
      paths = [ ./src ];
      extensions = [ "php" ];
    };
  };

  services.postgres = {
    enable = true;
    initialDatabases = [{ name = "my_app_db"; }];
    listen_addresses = "127.0.0.1";
    port = 5432;
  };
}
