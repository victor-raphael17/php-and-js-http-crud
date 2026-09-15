### To run this project

1. Clone the repository
2. Enter the `frontend` folder and run `npm install` followed by `npm run build`
3. Go back to the root folder and run `docker compose up -d --build --force-recreate`
4. Solve ports conflicts changing the ports bind in /compose.yaml or deleting the original container that's using the specific port
5. Solve any remaining conflict with `docker container rm -f $(docker ps -aq)` followed by `docker system prune -af --volumes` (warning: this deletes your docker images, volumes and containers)
