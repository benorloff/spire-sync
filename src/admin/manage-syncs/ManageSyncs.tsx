import React from "react";
import { __ } from "@wordpress/i18n";
import { Navigator, TabPanel } from "@wordpress/components";

const ManageSyncs: React.FC = () => {
  return (
    <div>
      <h1>{__("Manage Syncs", "spire-sync")}</h1>
      <TabPanel
        tabs={[
          {
            name: "products",
            title: "Products",
          },
          {
            name: "orders",
            title: "Orders",
          },
          {
            name: "customers",
            title: "Customers",
          },
        ]}
      >
        {(tab) => <p>{tab.title}</p>}
      </TabPanel>
    </div>
  );
};

export default ManageSyncs;
