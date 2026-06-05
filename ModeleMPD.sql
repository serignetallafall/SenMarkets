-- ----------------------------------------------------------
-- Script MYSQL pour mcd 
-- ----------------------------------------------------------


-- ----------------------------
-- Table: orders
-- ----------------------------
CREATE TABLE orders (
  id INT NOT NULL,
  reference VARCHAR(20) NOT NULL,
  status ENUM('UNKNOWN'),
  payment_method ENUM('UNKNOWN') NOT NULL,
  client_name VARCHAR(100) NOT NULL,
  client_email VARCHAR(150) NOT NULL,
  client_phone VARCHAR(25) NOT NULL,
  shipping_address VARCHAR(255) NOT NULL,
  shipping_city VARCHAR(100) NOT NULL,
  shipping_country VARCHAR(100) NOT NULL,
  subtotal INT NOT NULL,
  shipping_cost INT NOT NULL,
  total INT NOT NULL,
  notes TEXT,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT orders_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: order_items
-- ----------------------------
CREATE TABLE order_items (
  id INT NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_price INT NOT NULL,
  quantity SMALLINT NOT NULL,
  subtotal INT NOT NULL,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  CONSTRAINT order_items_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: products
-- ----------------------------
CREATE TABLE products (
  id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  category ENUM('UNKNOWN') NOT NULL,
  price INT NOT NULL,
  original_price INT,
  stock SMALLINT NOT NULL,
  emoji VARCHAR(10) NOT NULL,
  artisan VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  is_active BINARY(1) NOT NULL,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT products_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: contained_by
-- ----------------------------
CREATE TABLE contained_by (
  id INT NOT NULL,
  id_orders INT NOT NULL,
  CONSTRAINT contained_by_PK PRIMARY KEY (id, id_orders),
  CONSTRAINT contained_by_id_FK FOREIGN KEY (id) REFERENCES products (id),
  CONSTRAINT contained_by_id_orders_FK FOREIGN KEY (id_orders) REFERENCES orders (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: is
-- ----------------------------
CREATE TABLE is (
  id INT NOT NULL,
  id_products INT NOT NULL,
  CONSTRAINT is_PK PRIMARY KEY (id, id_products),
  CONSTRAINT is_id_FK FOREIGN KEY (id) REFERENCES order_items (id),
  CONSTRAINT is_id_products_FK FOREIGN KEY (id_products) REFERENCES products (id)
)ENGINE=InnoDB;


-- ----------------------------
-- Table: has
-- ----------------------------
CREATE TABLE has (
  id INT NOT NULL,
  id_orders INT NOT NULL,
  CONSTRAINT has_PK PRIMARY KEY (id, id_orders),
  CONSTRAINT has_id_FK FOREIGN KEY (id) REFERENCES order_items (id),
  CONSTRAINT has_id_orders_FK FOREIGN KEY (id_orders) REFERENCES orders (id)
)ENGINE=InnoDB;