import type {NodePlopAPI} from "plop";
import {reactComponent} from "./templates/react";
import {
  codeigniterController,
  codeigniterCrud,
  codeigniterEntity,
  codeigniterMigration,
  codeigniterModel,
  codeigniterRepository,
  codeigniterService
} from "./templates/codeigniter";

export default function (plop: NodePlopAPI) {
  // react component: admin, public, shared
  // react page: admin, public, shared
  // admin, public, shared run npm script
  // shared shadcn add

  // Create Component Generator
  plop.setGenerator("Backend CRUD", codeigniterCrud);
  plop.setGenerator("Frontend Component", reactComponent);
  plop.setGenerator("Backend Migration", codeigniterMigration);
  plop.setGenerator("Backend Controller", codeigniterController);
  plop.setGenerator("Backend Service", codeigniterService);
  plop.setGenerator("Backend Repository", codeigniterRepository);
  plop.setGenerator("Backend Model", codeigniterModel);
  plop.setGenerator("Backend Entity", codeigniterEntity);
}
